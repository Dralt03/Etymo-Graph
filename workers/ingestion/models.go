package main

// WordRecord maps to the `words` table in MySQL.
type WordRecord struct {
	Lemma      string
	Language   string
	POS        string
	Source     string
	Definition string
}

// EtymologyRecord maps to the `etymologies` table in MySQL.
type EtymologyRecord struct {
	WordLemma      string
	ParentLemma    string
	LanguageOrigin string
	RelationType   string
	Source         string
}

// WikiPage holds the raw title and wikitext from a single Wiktionary XML <page>.
type WikiPage struct {
	Title string
	Text  string
}
