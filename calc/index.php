<?php
//Соеденяемся с БД
try {
    $pdo = new PDO('mysql:host=localhost;dbname=calc', 'mysql', 'mysql');
} catch (PDOException $e) {
    echo "Невозможно установить соединение с базой данных" . $e->getMessage();
}
//выполняем вычисление
if (isset($_POST['submit'])) {
    $inputExpression = $_POST['text'];
    //добавим защиту от дурака
    $tokens = token_get_all("<?php {$inputExpression}");
    $expression = '';
    foreach ($tokens as $token) {
        if (is_string($token)) {
            if (in_array($token, array('(', ')', '+', '-', '/', '*'), true))
                $expression .= $token;
            continue;
        }
        list($id, $text) = $token;
        if (in_array($id, array(T_DNUMBER, T_LNUMBER)))
            $expression .= $text;
    }
    //выполняесм пхп код в строке
    eval("\$result = {$expression};");
    //сохраняем в БД выражение и результат
    $stmt = $pdo->prepare("INSERT INTO expressions(`expression`, `result`) VALUES(?, ?)");
    $stmt->execute([$expression, $result]);
}
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Калькулятор</title>
</head>
<body>

<div class="container">
    <h1 class="text-center">Калькулятор с возможностью сохранения данных</h1>
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="/">
                <input type="text" name="text" autofocus>
                <input type="submit" name="submit">
                <p>Ответ: <?= $result; ?></p>
            </form>
        </div>
        <div class="col-md-6">
            <?php
            //выводим выражение и результат из БД в обр порядке
            $stmt = $pdo->query("SELECT `expression`, `result` FROM expressions ORDER BY id DESC LIMIT 20");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $key => $value) {
                echo "<p>" . $value['expression'] . '=' . $value['result'] . "</p>";
            }
            ?>
        </div>
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>