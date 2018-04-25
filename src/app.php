<?php

namespace GuzzleTest;

class App {

    public function run()
    {
        $html = $this->getRedditHtml();

        $titles = $this->getRedditArticleTitles($html);

        $this->storeRedditArticleTitles($titles);

        $article_titles = $this->retrieveRedditArticleTitles();

        $this->display($article_titles);
    }

    public function getRedditHtml()
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://www.reddit.com', [
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/532.5 (KHTML, like Gecko) Chrome/4.0.249.0 Safari/532.5']
        ]);

        $html = (string)$res->getBody();

        return $html;
    }

    public function getRedditArticleTitles($html)
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

        $titles = array();

        $filterString = 'div > div > div > div > div > div > div > div > div > div > div > div > div > div > div > div > div > span > a';
        //$filterString = 'a';

        foreach($crawler->filter($filterString) as $content) {

            if(isset($content->getElementsByTagName('h2')->item(0)->nodeValue)) {

                $titles[$content->getAttribute('href')] = $content->getElementsByTagName('h2')->item(0)->nodeValue;
            }
        }

        return $titles;
    }

    public function storeRedditArticleTitles($titles)
    {
        $link = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);

        if($link === false){
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        foreach($titles as $article_url => $article_title) {

            $dt = new \DateTime('now');

            $res = mysqli_query($link, "SELECT id FROM reddit_articles WHERE title = '" . mysqli_real_escape_string($link, $article_title) . "'");

            if($res->num_rows == 0) {

                $sql = "INSERT INTO reddit_articles (title, url, tstamp) VALUES ('" . mysqli_real_escape_string($link, $article_title) . "','" . mysqli_real_escape_string($link, $article_url) . "','" . $dt->format('Y-m-d') . "')";

                mysqli_query($link, $sql);
            }
        }

        mysqli_close($link);
    }

    public function retrieveRedditArticleTitles()
    {
        $link = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);

        if($link === false){
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        $res = mysqli_query($link, "SELECT id,title,url FROM reddit_articles ORDER BY tstamp DESC");

        $article_titles = array();

        while($row = $res->fetch_assoc()) {

            $article_titles[$row['url']] = $row['title'];
        }

        return $article_titles;
    }

    public function display($article_titles)
    {
        $html = '<html><head><title>Guzzle Example Test</title></head><body><table><tr><th>Title</th></tr>';

        foreach($article_titles as $article_url => $article_title) {

            $html = $html . '<tr><td><a href="' . $article_url . '">' . $article_title . '</a></td></tr>';
        }

        $html = $html . '</table></body></html>';

        echo $html;
    }
}