#simplejson is not standard (pip install simplejson)
import urllib2, simplejson, sys, time, codecs

datetime = str(time.strftime("%Y-%m-%dT%H%M"))

#function to create and manage a progress bar
def update_progress(progress, status):
  barLength = 50
  status = status[0:20].ljust(20, " ")
  if isinstance(progress, int):
      progress = float(progress)
  if not isinstance(progress, float):
      progress = 0
      status = "error: progress var must be float\r\n"
  if progress < 0:
      progress = 0
      status = "Halt...\r\n"
  if progress >= 1:
      progress = 1
      status = "Done...\r\n"
  block = int(round(barLength*progress))
  text = "\rPercent: [{0}] {1}% {2}".format("#"*block + "-"*(barLength-block), progress*100, status)
  sys.stdout.write(text)
  sys.stdout.flush()

#Load every word from SCOWL and friends
words = open("en_US.dic") #Uses Hunspell dic http://wordlist.aspell.net/dicts/
words = words.read().split("\n")

#Make the output file have column headers
text_file = open("visfarOutput" + datetime + ".csv", "w")
text_file.write("word,definition,.com,.community,.company,.computer,.business,.center,.city,.enterprises,.host,.hosting,.limited,.net,.org,.solutions,.tech,.technology,.town,.co,.io,.compare,.corp,.inc,.ltd,\n")
text_file.close()

#Beginning
print "Checking all words in SCOWL dictionary (" + str(len(words)) + " words) against Wordnik and Domainr to produce database of words, their definition, and if a few domains of that word are available"
update_progress(0, "Starting")

goodWords = []

count = 0.0

#Check words
for word in words:
  count += 1.0
  #See if the word is on Wordnik (because we need the description)
  req = urllib2.Request(
    "http://api.wordnik.com:80/v4/word.json/" + word + "/definitions?limit=1&includeRelated=true&useCanonical=false&includeTags=false&api_key=a2a73e7b926c924fad7001ca3111acd55af2ffabf50eb4ae5",
    None,
    {'user-agent':'zbee/visfar'}
  )
  opener = urllib2.build_opener()
  #try opening the data received from the website and reading the json inside
  try:
    json = opener.open(req)
    data = simplejson.load(json)
    #If at least 1 definition was found on Wordnik
    if len(data) > 0:
      #Add the word to the list of good words
      goodWords.append(word)
      #Clean up the word and its definition
      Sword = data[0]["word"].encode("utf-8")
      Stext = data[0]["text"].encode("utf-8")
      #Now search Domainr for the word
      req = urllib2.Request(
        "https://domainr.com/api/json/search?client_id=visfar&q=" + word + ".com",
        None,
        {'user-agent':'zbee/visfar'}
      )
      #Read the data from Domainr
      json = opener.open(req)
      data = simplejson.load(json)
      results = data["results"]
      domAvail = {
        ".com": "",
        ".community": "",
        ".company": "",
        ".computer": "",
        ".business": "",
        ".center": "",
        ".city": "",
        ".enterprises": "",
        ".host": "",
        ".hosting": "",
        ".limited": "",
        ".net": "",
        ".org": "",
        ".solutions": "",
        ".tech": "",
        ".technology": "",
        ".town": "",
        ".co": "",
        ".io": "",
        ".compare": "",
        ".corp": "",
        ".inc": "",
        ".ltd": ""
      }
      #For each search result
      for tld in results:
        domain = tld["domain"].encode("utf-8")
        #Check if the search result has a period in it (apparently some don't?)
        if "." in domain:
          #Get the TLD part
          domain = domain.split(".")
          domain = "." + domain[1]
          #The first letter of the availability of this domain
          available = tld["availability"][:1]
          #Add the TLD and the availability code to the domAvail variable
          domAvail[domain] = available.encode("utf-8")
      domAvailT = ""
      for (domain, availability) in domAvail.items():
        domAvailT += availability + ","
      with open("visfarOutput" + datetime + ".csv", 'a') as f:
        #Add the word, its definition, and its domain availability to the output
        f.write(Sword + "," + Stext.replace(",", "`") + "," + domAvailT + "\n")
  #If the data from the website cannot be read (404, primarily)
  except urllib2.HTTPError as e:
    #Just skip it, no one cares in this case
    pass
  update_progress(count/len(words), word) 
print "Good words: " + str(len(goodWords)) + "; Percentage of SCOWL words on Wordnik: " + str(len(goodWords)/count*100) + "%"