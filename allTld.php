<?php
function handleUTF8 ($code) {
  return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function($match) {
    list($utf8) = $match;
    $entity = mb_convert_encoding($utf8, 'HTML-ENTITIES', 'UTF-8');
    return $entity;
  },
  $code);
}

$word = $_GET["word"];
$word = strip_tags($word);
$word = handleUTF8($word);

$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$page = intval($page);
$page = $page > 17 ? 17 : $page;
$page = $page < 1 ? 1 : $page;
?>

<!DOCTYPE html>
<html>
  <head>
    <title>visfar: <?=$word?></title>
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
      Here are <?=$page*100-100?>-<?=$page*100-1?> of the 1,736 domains for
      <i><?=$word?></i> (page <?=$page?> of 17).
      <br>
      <a href="http://beta.zbee.me/visfar">
        Take me home, this is more boring than I thought it would be.
      </a>
    </div>
    <div class="bar grey">
      <h1>Domain availability</h1>
      <?=$page>1 ? "<a href='http://beta.zbee.me/visfar/allTld.php?word="
      .$word."&page=".($page-1)."'>Previous Page</a> " : ""?>
      <?=$page<17 ? "<a href='http://beta.zbee.me/visfar/allTld.php?word="
      .$word."&page=".($page+1)."'>Next Page</a>" : ""?>
      <table>
        <tr>
          <th>TLD</th>
          <th>Domain</th>
          <th>Status</th>
        </tr>
        <?php
          $tlds = file_get_contents("tld");
          $tlds = explode("\n", $tlds);
          $tld = [];
          
          for ($x = $page * 100 - 100; $x <= $page * 100 - 1; $x++) {
            $tld[$x] = handleUTF8($tlds[$x]);
          }

          $skip = [];
          
          $count = 0;

          foreach ($tld as $key => $tld) {
            if (isset($skip[$tld])) continue;
            
            if (strpos($tld, "(") !== false) {
              $split = explode("(", $tld);
              $subs = $split[1];
              $tld = $split[0];
              $split = null;
            }
            
            $search = file_get_contents(
              "https://domainr.com/api/json/search?client_id=visfar&q="
              .$word.$tld
            );
            $search = json_decode($search)->results;
            
            foreach ($search as $domain) {
              $domTld = explode(".", $domain->domain)[1];
              $skip[$domTld] = "";
            
              echo "
                <tr>
                  <td>.$domTld</td>
                  <td><a href='$domain->domain'>$domain->domain</a></td>
                  <td>
                    <a href='$domain->register_url'>$domain->availability</a>
                  </td>
                </tr>
              ";
            }
          }
        ?>
      </table>
      <?=$page>1 ? "<a href='http://beta.zbee.me/visfar/allTld.php?word="
      .$word."&page=".($page-1)."'>Previous Page</a> " : ""?>
      <?=$page<17 ? "<a href='http://beta.zbee.me/visfar/allTld.php?word="
      .$word."&page=".($page+1)."'>Next Page</a>" : ""?>
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