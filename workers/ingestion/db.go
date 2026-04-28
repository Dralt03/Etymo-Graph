package main

import (
	"database/sql"
	"fmt"
	"log"
	"strings"
)

const batchSize = 500

// BulkInsertWords upserts a batch of WordRecords into the `words` table.
// Uses ON DUPLICATE KEY UPDATE to skip duplicate (lemma, language, pos) rows.
func BulkInsertWords(db *sql.DB, words []WordRecord) error {
	if len(words) == 0 {
		return nil
	}

	// Build a multi-row INSERT
	placeholders := make([]string, len(words))
	args := make([]interface{}, 0, len(words)*5)

	for i, w := range words {
		placeholders[i] = "(?, ?, ?, ?, ?)"
		args = append(args, w.Lemma, w.Language, w.POS, w.Source, w.Definition)
	}

	query := fmt.Sprintf(
		"INSERT INTO words (lemma, language, pos, source, definition, created_at, updated_at) VALUES %s ON DUPLICATE KEY UPDATE source=VALUES(source)",
		strings.Join(placeholders, ","),
	)

	// Append timestamps for each row
	rows := len(words)
	tsPlaceholders := make([]string, rows)
	newArgs := make([]interface{}, 0, rows*7)
	for i, w := range words {
		tsPlaceholders[i] = "(?, ?, ?, ?, ?, NOW(), NOW())"
		newArgs = append(newArgs, w.Lemma, w.Language, w.POS, w.Source, w.Definition)
	}
	_ = args

	query = fmt.Sprintf(
		"INSERT INTO words (lemma, language, pos, source, definition, created_at, updated_at) VALUES %s ON DUPLICATE KEY UPDATE definition=VALUES(definition)",
		strings.Join(tsPlaceholders, ","),
	)

	_, err := db.Exec(query, newArgs...)
	return err
}

// BulkInsertEtymologies upserts etymology relationships.
// Since we only have lemma strings (not IDs) at parse time, we resolve word IDs
// via a SELECT and then insert in batch.
func BulkInsertEtymologies(db *sql.DB, etymologies []EtymologyRecord) error {
	if len(etymologies) == 0 {
		return nil
	}

	for _, e := range etymologies {
		var wordID, parentID int64

		err := db.QueryRow("SELECT id FROM words WHERE lemma = ? LIMIT 1", e.WordLemma).Scan(&wordID)
		if err != nil {
			continue // word not yet in DB, skip this etymology
		}

		// Ensure parent word exists (insert if needed)
		_, insertErr := db.Exec(
			"INSERT IGNORE INTO words (lemma, language, pos, source, created_at, updated_at) VALUES (?, ?, '', 'wiktionary', NOW(), NOW())",
			e.ParentLemma, e.LanguageOrigin,
		)
		if insertErr != nil {
			continue
		}

		err = db.QueryRow("SELECT id FROM words WHERE lemma = ? LIMIT 1", e.ParentLemma).Scan(&parentID)
		if err != nil || parentID == wordID {
			continue // self-reference guard
		}

		_, err = db.Exec(
			`INSERT INTO etymologies (word_id, parent_word_id, relation_type, language_origin, source, created_at, updated_at)
			 VALUES (?, ?, ?, ?, 'wiktionary', NOW(), NOW())
			 ON DUPLICATE KEY UPDATE relation_type=VALUES(relation_type)`,
			wordID, parentID, e.RelationType, e.LanguageOrigin,
		)
		if err != nil {
			log.Printf("Warning: etymology insert failed for %s → %s: %v", e.WordLemma, e.ParentLemma, err)
		}
	}
	return nil
}

// InsertWordsBatched processes a slice of WordRecords in batches of batchSize.
func InsertWordsBatched(db *sql.DB, words []WordRecord) {
	for i := 0; i < len(words); i += batchSize {
		end := i + batchSize
		if end > len(words) {
			end = len(words)
		}
		if err := BulkInsertWords(db, words[i:end]); err != nil {
			log.Printf("Word batch insert error: %v", err)
		}
	}
}
