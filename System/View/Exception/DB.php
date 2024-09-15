
<!DOCTYPE html>
<html>
    <head>
        <title>DB Error - PDOException</title>
        <!--    <link rel="stylesheet" href="style.css">-->
        <style>
            .error-container {
                background-color: #000000;
                color: yellow;
                border-left: 6px solid #ebccd1;
                padding: 20px;
                margin: 00px;
            }

            .error-container h1 {
                color: Red;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>DB Exception </h1>
            <p><b>Message:</b> <?php echo $Message; ?></p>
            <p><b>Code:</b> <?php echo $Code; ?></p>
            <p><b>File@Line:</b> <?php echo $File . ':' . $Line; ?></p>
            <p><b>Query:</b> <?php echo $Query; ?></p>
            <pre><?php var_dump($Trace); ?></pre>
            <h2>Full Data</h2>
            <pre><?php print_r($E); ?></pre>
        </div>
    </body>
</html>