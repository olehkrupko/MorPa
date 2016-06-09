<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<?php if(Yii::app()->user->name == "Guest") {
    header("Location: index.php?r=site/login");
} ?>

<?php if ($_SERVER['REQUEST_METHOD'] == "POST") {    
    if (substr($_POST['link'], 0, 4) != "www." && substr($_POST['link'], 0, 4) != "http" ) 
        $_POST['link'] = "www.".$_POST['link'];
    
    $ch = curl_init();

    // установка URL и других необходимых параметров
    curl_setopt($ch, CURLOPT_URL, "http://ajax.googleapis.com/ajax/services/feed/lookup?v=1.0&q=".$_POST['link']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $responseJSON = curl_exec($ch);
    $response = json_decode ($responseJSON);
    
    if ($response->responseDetails != "no associated feed") {
        $feedURL = $response->responseData->url;

        $sql = "
            INSERT INTO feed (feed.feedURL, feed.user) VALUES ('$feedURL', '".Yii::app()->user->name."');
        ";
        $command = Yii::app()->db->createCommand($sql)->execute();
        
        $sql = "
            DELETE feed 
            FROM feed
            LEFT OUTER JOIN (
               SELECT MIN(id) as id, feedURL
               FROM feed 
               GROUP BY feedURL
            ) as KeepRows ON
               feed.id = KeepRows.id
            WHERE
               KeepRows.id IS NULL
        ";
        //echo $sql;
        $command = Yii::app()->db->createCommand($sql)->execute();
        
        echo 'Added! '.$feedURL.'<br><br><br>';
    } else echo "<h2>Ссылка не может быть использована</h2>";
    
    NewsEntry::feedToNews($feedURL);
    NewsEntry::feedToList($feedURL);
    
}

//TODO: error message

?>

<form method="post">
    <input type="text" name="link" value="<?php 
        if ($_POST['link'])
            echo $_POST['link'];
        else
            echo "dou.ua";
    ?>" >
    <input type="submit">
</form>