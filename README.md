# Etymo Graph

Etymo Graph is a project designed to map the etymological relationships of words over time and analyze vector drift. It combines traditional relational data representing word relationships (from BabelNet/Wiktionary) with historical word embeddings (from Google Books Ngram/Common Crawl).

## Architecture

This project is built using:
- **Larvel**: The core web application framework for presenting the graph.
- **MySQL**: Relational database storing the core etymological graph structure (Words, Synsets, WordSynsets, Etymologies).
- **OpenSearch**: Vector database storing dense vectors and frequencies of words over different years, enabling K-Nearest Neighbor (KNN) search for semantic vector drift analysis.
- **Go Ingestion Worker**: A high-performance background worker designed to stream, parse, vectorize, and bulk-load massive XML dumps (e.g., Wiktionary) without running out of memory.

## Getting Started

### Prerequisites
- Docker & Docker Compose
- Go 1.22+
- PHP 8.2+ (or use the provided Docker container)

### Quick Start

1. **Start the Database Infrastructure:**
   The project uses Docker to spin up MySQL and OpenSearch automatically.
   ```bash
   docker-compose up -d
   ```

2. **Data Ingestion:**
   Navigate into the `workers/ingestion` directory to run the Go pipeline. This pipeline will download the Wiktionary data stream, vectorize it, and populate both MySQL and OpenSearch.
   ```bash
   cd workers/ingestion
   go run .
   ```

3. **Start the Web Application:**