package main

import (
	"database/sql"
	"flag"
	"fmt"
	"log"
	"os"
	"path/filepath"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"github.com/joho/godotenv"
)

func main() {
	// --- CLI flags ---
	dumpPath := flag.String("dump", "", "Path to Wiktionary XML dump (.xml or .xml.bz2)")
	flag.Parse()

	if *dumpPath == "" {
		fmt.Fprintln(os.Stderr, "Usage: go run . -dump /path/to/enwiktionary-latest-pages-articles.xml.bz2")
		fmt.Fprintln(os.Stderr, "\nDownload the dump from:")
		fmt.Fprintln(os.Stderr, "  https://dumps.wikimedia.org/enwiktionary/latest/enwiktionary-latest-pages-articles.xml.bz2")
		os.Exit(1)
	}

	envPath := findEnvFile()
	if envPath == "" {
		log.Println(".env file not found, using environment default settings")
	} else {
		godotenv.Load(envPath)
		log.Printf("Loaded config from: %s", envPath)
	}

	db := connectToDatabase()
	defer db.Close()

	// Tune connection pool for sustained bulk writes
	db.SetMaxOpenConns(10)
	db.SetMaxIdleConns(5)
	db.SetConnMaxLifetime(5 * time.Minute)

	// --- Stream and ingest ---
	fmt.Printf("Opening dump: %s\n", *dumpPath)
	if err := ingest(db, *dumpPath); err != nil {
		log.Fatalf("Ingestion failed: %v", err)
	}

	fmt.Println("Ingestion complete.")
}

// ingest streams the Wiktionary XML dump and bulk-inserts into MySQL.
func ingest(db *sql.DB, dumpPath string) error {
	pages, err := StreamPages(dumpPath)
	if err != nil {
		return fmt.Errorf("opening dump: %w", err)
	}

	var (
		totalPages int
		wordBatch  []WordRecord
		etymBatch  []EtymologyRecord
		start      = time.Now()
	)

	for page := range pages {
		totalPages++

		words, etyms := ParsePage(page)
		wordBatch = append(wordBatch, words...)
		etymBatch = append(etymBatch, etyms...)

		// Flush word batch every batchSize records
		if len(wordBatch) >= batchSize {
			InsertWordsBatched(db, wordBatch)
			wordBatch = wordBatch[:0]
		}

		// Progress report every 10 000 pages
		if totalPages%10_000 == 0 {
			elapsed := time.Since(start).Round(time.Second)
			fmt.Printf("  → Processed %d pages in %s\n", totalPages, elapsed)
		}
	}

	// Flush remaining words
	if len(wordBatch) > 0 {
		InsertWordsBatched(db, wordBatch)
	}

	fmt.Printf("\n Pages parsed: %d\n", totalPages)
	fmt.Printf(" Resolving %d etymology relationships...\n", len(etymBatch))

	if err := BulkInsertEtymologies(db, etymBatch); err != nil {
		log.Printf(" Some etymology records failed: %v", err)
	}

	return nil
}

// connectToDatabase opens and pings the MySQL connection using Laravel's .env vars.
func connectToDatabase() *sql.DB {
	if os.Getenv("DB_CONNECTION") == "sqlite" {
		log.Fatalf("The Go pipeline requires MySQL. Found DB_CONNECTION=sqlite in .env.\n" +
			"   Switch to MySQL (docker-compose up -d) and update .env before running the ingestion worker.")
	}

	host := envOrDefault("DB_HOST", "127.0.0.1")
	port := envOrDefault("DB_PORT", "3306")
	user := envOrDefault("DB_USERNAME", "root")
	password := os.Getenv("DB_PASSWORD")
	dbName := os.Getenv("DB_DATABASE")

	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&multiStatements=true&charset=utf8mb4&collation=utf8mb4_unicode_ci",
		user, password, host, port, dbName)

	fmt.Printf("Connecting to MySQL at %s:%s (db: %s)\n", host, port, dbName)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("Failed to open database: %v", err)
	}

	if err = db.Ping(); err != nil {
		log.Fatalf("Cannot reach MySQL: %v", err)
	}

	fmt.Println("Database connection established.")
	return db
}

func findEnvFile() string {
	dir, err := os.Getwd()
	if err != nil {
		return ""
	}

	for {
		candidate := filepath.Join(dir, ".env")
		if _, err := os.Stat(candidate); err == nil {
			return candidate
		}
		parent := filepath.Dir(dir)
		if parent == dir {
			break
		}
		dir = parent
	}
	return ""
}

func envOrDefault(key, defaultVal string) string {
	if val := os.Getenv(key); val != "" {
		return val
	}
	return defaultVal
}
