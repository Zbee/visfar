## visfar
visfar is a sort of remake of http://randomdotcom.com

On loading index.php it will display a random word from wordnik (currently using their example api key) and the availability as a domain with 20 different TLDs.

If the allTld.php file is visited for a word then it will display 1 of the 17 pages each showing the availability for the word as a domain for 100 TLDs (there are a total of 1,736 currently tracked by domainr).

A .csv file can be generated with dicToCSV.py using the latest version of the Hunspell dictionary (here: http://wordlist.aspell.net/dicts/) and the availability for each word as a domain for 20 TLDs.

### Todo
- Add actual tracking capability to index.php
  - Never show the same random word twice to the same person
  - How many people are served everyday
- Add caching of the data served (can build own dictionary)
- Make dicToCSV.py fetch the most recent dictionary
- Upload a generated .csv