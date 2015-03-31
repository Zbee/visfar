<?php
function handleUTF8 ($code) {
  return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function($match) {
    list($utf8) = $match;
    $entity = mb_convert_encoding($utf8, 'HTML-ENTITIES', 'UTF-8');
    return $entity;
  },
  $code);
}

$randoms["word"] = file_get_contents("http://api.wordnik.com/v4/words.json/randomWord?hasDictionaryDef=true&minCorpusCount=0&maxCorpusCount=-1&minDictionaryCount=1&maxDictionaryCount=-1&minLength=5&maxLength=-1&api_key=a2a73e7b926c924fad7001ca3111acd55af2ffabf50eb4ae5");
$randoms["word"] = json_decode($randoms["word"]);
$randoms["word"] = handleUTF8($randoms["word"]->word);

$randoms["definition"] = file_get_contents("http://api.wordnik.com:80/v4/word.json/".$randoms["word"]."/definitions?limit=2&includeRelated=true&useCanonical=false&includeTags=false&api_key=a2a73e7b926c924fad7001ca3111acd55af2ffabf50eb4ae5");
$randoms["definition"] = $d = json_decode($randoms["definition"])[0];
$randoms["definition"] = handleUTF8($randoms["definition"]->text);
if (isset($d->partOfSpeech)) $randoms["definition"] .= " (" . $d->partOfSpeech . ")";

file_put_contents("theList.txt", file_get_contents("theList.txt")."\n".$randoms["word"].";".str_replace(";", ":", $randoms["definition"]));

$randoms["tlds"] = file_get_contents("https://domainr.com/api/json/search?client_id=visfar&q=".$randoms["word"].".com");
$randoms["tlds"] = json_decode($randoms["tlds"])->results;

$table = "";

for ($x = 0; $x < count($randoms["tlds"]); $x++) {
  $randoms[$x] = [
    ".".explode(".", $randoms["tlds"][$x]->domain)[1],
    $randoms["tlds"][$x]->domain,
    $randoms["tlds"][$x]->availability,
    $randoms["tlds"][$x]->register_url
  ];
  
  file_put_contents("theList.txt", file_get_contents("theList.txt").";".explode(".", $randoms["tlds"][$x]->domain)[1].":".$randoms["tlds"][$x]->availability[0]);
  
  $table .= "
  <tr>
    <td>".$randoms[$x][0]."</td>
    <td><a href=\"http://".$randoms[$x][1]."\">".$randoms[$x][1]."</a></td>
    <td><a href=\"http://".$randoms[$x][3]."\">".$randoms[$x][2]."</a></td>
  </tr>
  ";
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>visfar</title>
    <style type="text/css">
      body, html {
        background: #F6F6F6;
        color: #5C5C5C;
        font: 150% "Ubuntu", Helvetica;
        padding: 0px;
        margin: 0px;
        overflow-x: hidden;
      }

      .bar {
        color: #1D1F20;
        font: 100% "Ubuntu", Helvetica;
        display: block;
        width: 95%;
        padding: 5% 2.5%;
        overflow-x: hidden;
      }
      .bar table {
        width: 100%;
        border-collapse: collapse;
      }
      .bar table tr {
        border-bottom: 1px solid #5C5C5C;
      }
      .bar table tr th {
        text-align: left;
        padding: 5px;
      }
      .bar table tr td {
        padding: 5px;
      }
      .bar table tr:hover {
        background: rgba(0, 0, 0, 0.1);
      }

      .grey {
        background: #999999;
      }
      .grey a, .grey a:hover, .grey a:visited {
        color: #6E61B0;
        text-decoration: underline;
        cursor: pointer;
      }

      .purple {
        background: #6E61B0;
      }
      .purple a, .purple a:hover, .purple a:visited {
        color: #999999;
        text-decoration: underline;
        cursor: pointer;
      }

      h1 {
        font: 200% "Ubuntu", Helvetica;
        margin: 0px 0px 10px 0px;
        padding: 0px;
        width: 100%;
        border-bottom: 1px solid #1D1F20;
      }
      
      .small {
        font: 66% "Ubuntu", Helvetica;
      }
    </style>
  </head>
  <body>
    <div class="bar purple">
      <h1>Hello, welcome to visfar</h1>
      How are you today? Good? Yeah? I don't care.
      <br><br>
      Here's a random word for you:
      <i>
        <?=$randoms["word"]?>
        <br>
        <?=$randoms["definition"]?>
      </i>
    </div>
    <div class="bar grey">
      <h1>Domain availability</h1>
      <table>
        <tr>
          <th>TLD</th>
          <th>Domain</th>
          <th>Status</th>
        </tr>
        <?=$table?>
      </table>
      <br>
      <a href="allTld.php?word=<?=$randoms["word"]?>&page=1">
        All 1,736 domains for this word and their availability
      </a>
    </div>
    <div class="bar purple">
      The full list of all 1.6 thousand English 1-word domains:
      <a>.csv</a>, <a>.txt</a>
    </div>
    <div class="bar grey small">
      Made by Ethan Henderson (Zbee) 2015-03-29 after being unimpressed by 
      <a href="http://www.randomdotcom.com/">randomdotcom.com</a>.
      <br>
      This is open-source on <a href="http://github.com/zbee/visfar">GitHub</a>.
    </div>
    <div class="bar purple small">
      This result was served in the past 7 days, so you are viewing cached data.
      The script took 0.001 seconds to run.
      519,509 people have been served today.
      4,594 different words have been served today.
      84% of servings today have been cached.
    </div>
  </body>
</html>