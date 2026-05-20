#!/bin/bash

URL="http://cyber.blog:8000/articles/search?query=test"

echo "Avvio attacco DoS simulato su $URL"

for i in {1..30}
do
  STATUS=$(curl -o /dev/null -s -w "%{http_code}" "$URL")
  echo "Richiesta $i -> HTTP $STATUS"
done

echo "Test terminato"
