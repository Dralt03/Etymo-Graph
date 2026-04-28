package main

import (
	"compress/bzip2"
	"encoding/xml"
	"io"
	"os"
	"regexp"
	"strings"
)

// --- XML Streaming ---

// xmlPage mirrors the Wiktionary XML structure for streaming decoding.
type xmlPage struct {
	Title string `xml:"title"`
	NS    int    `xml:"ns"`
	Text  string `xml:"revision>text"`
}

// StreamPages opens a .xml or .xml.bz2 dump file and streams WikiPage values
// through the returned channel. It reads one <page> at a time so it never
// loads the whole multi-GB dump into RAM.
func StreamPages(path string) (<-chan WikiPage, error) {
	f, err := os.Open(path)
	if err != nil {
		return nil, err
	}

	var reader io.Reader = f
	if strings.HasSuffix(path, ".bz2") {
		reader = bzip2.NewReader(f)
	}

	ch := make(chan WikiPage, 256)

	go func() {
		defer f.Close()
		defer close(ch)

		decoder := xml.NewDecoder(reader)
		for {
			tok, err := decoder.Token()
			if err == io.EOF {
				break
			}
			if err != nil {
				break
			}

			// Only process <page> start elements
			if se, ok := tok.(xml.StartElement); ok && se.Name.Local == "page" {
				var p xmlPage
				if err := decoder.DecodeElement(&p, &se); err != nil {
					continue
				}
				// Namespace 0 = main article pages only (skip talk pages etc.)
				if p.NS == 0 && p.Text != "" {
					ch <- WikiPage{Title: p.Title, Text: p.Text}
				}
			}
		}
	}()

	return ch, nil
}

// --- Wikitext Parsing ---

var (
	// Match language section headers: == English ==
	reLangSection = regexp.MustCompile(`(?m)^==\s*([^=][^=]*?)\s*==\s*$`)
	// Match POS subsection headers: === Noun ===  or ==== Noun ====
	rePOSSection = regexp.MustCompile(`(?m)^={3,4}\s*(Noun|Verb|Adjective|Adverb|Pronoun|Preposition|Conjunction|Interjection|Prefix|Suffix|Particle)\s*={3,4}\s*$`)
	// Match definition lines: # text (but not ## or #*)
	reDefinition = regexp.MustCompile(`(?m)^#([^#*:])(.*)`)
	// Match etymology templates: {{bor|en|la|computare}} {{der|en|grc|...}} {{inh|en|...}}
	reEtymTemplate = regexp.MustCompile(`\{\{(bor|der|inh|borrowed|derived|inherited)\|([^|]+)\|([^|]+)\|([^|}]*)`)
	// Strip wiki markup from definition text
	reWikiMarkup = regexp.MustCompile(`\[\[([^|\]]+\|)?([^\]]+)\]\]|\{\{[^}]+\}\}|'{2,3}`)
)

var knownPOS = map[string]bool{
	"Noun": true, "Verb": true, "Adjective": true, "Adverb": true,
	"Pronoun": true, "Preposition": true, "Conjunction": true,
	"Interjection": true, "Prefix": true, "Suffix": true, "Particle": true,
}

// relationTypeMap maps Wiktionary template names to our relation_type values.
var relationTypeMap = map[string]string{
	"bor":       "borrowed_from",
	"borrowed":  "borrowed_from",
	"der":       "derived_from",
	"derived":   "derived_from",
	"inh":       "inherited_from",
	"inherited": "inherited_from",
}

// ParsePage extracts WordRecords and EtymologyRecords from one Wiktionary page.
// A single page can contain multiple language sections (English, Latin, etc.)
func ParsePage(page WikiPage) ([]WordRecord, []EtymologyRecord) {
	var words []WordRecord
	var etymologies []EtymologyRecord

	// Split the wikitext into language sections
	langMatches := reLangSection.FindAllStringIndex(page.Text, -1)
	if len(langMatches) == 0 {
		return nil, nil
	}

	for i, match := range langMatches {
		// Determine the section boundary
		start := match[1]
		end := len(page.Text)
		if i+1 < len(langMatches) {
			end = langMatches[i+1][0]
		}

		langName := extractLangName(page.Text[match[0]:match[1]])
		langCode := langNameToCode(langName)
		sectionText := page.Text[start:end]

		// --- Extract POS + first definition ---
		pos := ""
		definition := ""

		posMatches := rePOSSection.FindAllStringIndex(sectionText, -1)
		for j, posMatch := range posMatches {
			posName := extractPOSName(sectionText[posMatch[0]:posMatch[1]])
			if !knownPOS[posName] {
				continue
			}
			if pos == "" {
				pos = strings.ToLower(posName)
			}

			// Find definition lines within this POS block
			posBlockEnd := len(sectionText)
			if j+1 < len(posMatches) {
				posBlockEnd = posMatches[j+1][0]
			}
			block := sectionText[posMatch[1]:posBlockEnd]
			defMatches := reDefinition.FindStringSubmatch(block)
			if defMatches != nil && definition == "" {
				definition = cleanDefinition(defMatches[1] + defMatches[2])
			}
		}

		// Always record the word even without a POS (it's in the dump)
		words = append(words, WordRecord{
			Lemma:      page.Title,
			Language:   langCode,
			POS:        pos,
			Source:     "wiktionary",
			Definition: definition,
		})

		// --- Extract etymology relationships ---
		etymMatches := reEtymTemplate.FindAllStringSubmatch(sectionText, -1)
		for _, em := range etymMatches {
			if len(em) < 5 {
				continue
			}
			templateName := em[1]
			// em[2] = destination language (same as langCode), em[3] = source language, em[4] = source word
			sourceLanguage := em[3]
			sourceWord := strings.TrimSpace(em[4])
			if sourceWord == "" {
				continue
			}
			relType, ok := relationTypeMap[templateName]
			if !ok {
				continue
			}
			etymologies = append(etymologies, EtymologyRecord{
				WordLemma:      page.Title,
				ParentLemma:    sourceWord,
				LanguageOrigin: sourceLanguage,
				RelationType:   relType,
				Source:         "wiktionary",
			})
		}
	}

	return words, etymologies
}

func extractLangName(header string) string {
	s := strings.TrimSpace(header)
	s = strings.Trim(s, "=")
	return strings.TrimSpace(s)
}

func extractPOSName(header string) string {
	s := strings.TrimSpace(header)
	s = strings.Trim(s, "=")
	return strings.TrimSpace(s)
}

func cleanDefinition(raw string) string {
	// Replace [[link|text]] with text, [[link]] with link
	clean := reWikiMarkup.ReplaceAllStringFunc(raw, func(m string) string {
		if strings.HasPrefix(m, "[[") {
			inner := strings.Trim(m, "[]")
			parts := strings.SplitN(inner, "|", 2)
			if len(parts) == 2 {
				return parts[1]
			}
			return parts[0]
		}
		return "" // strip templates and bold/italic
	})
	clean = strings.TrimSpace(clean)
	if len(clean) > 500 {
		clean = clean[:500]
	}
	return clean
}

// langNameToCode maps common Wiktionary language names to ISO 639-3 codes.
func langNameToCode(name string) string {
	mapping := map[string]string{
		"English":             "eng",
		"Latin":               "lat",
		"Ancient Greek":       "grc",
		"French":              "fra",
		"German":              "deu",
		"Spanish":             "spa",
		"Italian":             "ita",
		"Portuguese":          "por",
		"Dutch":               "nld",
		"Old English":         "ang",
		"Middle English":      "enm",
		"Old French":          "fro",
		"Proto-Germanic":      "gem",
		"Proto-Indo-European": "ine",
		"Arabic":              "ara",
		"Hebrew":              "heb",
		"Japanese":            "jpn",
		"Chinese":             "zho",
	}
	if code, ok := mapping[name]; ok {
		return code
	}
	// Fallback: lowercase first 3 chars
	if len(name) >= 3 {
		return strings.ToLower(name[:3])
	}
	return strings.ToLower(name)
}
