# solr-pdf-search

Start with:
docker-compose up -d

Add PDFs to www/pdfs directory

Index PDFs with:
docker-compose exec php php /var/www/html/index_pdfs.php

Open web page with search results:
http://localhost/search.php?q=ipsum

