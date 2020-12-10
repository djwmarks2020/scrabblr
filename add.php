<?php
require_once __DIR__ . '/autoload.php';

$playerNames = array_column(iterator_to_array(query('Select name From players')), 'name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turns = array_filter(array_map('trim', explode("\n", $_POST['scores'])));

    query(
        "Insert Into matches (match_date, first_player) Values (:match_date, :first_player)",
        [
            'match_date' => $_POST['match_date'] ?? null,
            'first_player' => $_POST['first_player'],
        ]
    );

    $matchId = lastId();
    $turnNumber = 1;

    foreach ($turns as $turn) {
        list ($first, $second) = preg_split('/[ ,]/', $turn);

        $sql = "
          Insert Into scores (match_id, turn, player_name, score)
          Values (:match_id, :turn, :player_name, :score)
        ";

        query($sql, [
            'match_id' => $matchId,
            'turn' => $turnNumber,
            'player_name' => $_POST['first_player'],
            'score' => $first,
        ]);

        query($sql, [
            'match_id' => $matchId,
            'turn' => $turnNumber,
            'player_name' => $playerNames[(array_search($_POST['first_player'], $playerNames) + 1) % 2],
            'score' => $second,
        ]);

        $turnNumber++;
    }

    header ('Location: match/?id=' . (int) $matchId);
    die;
}
?>

<!DOCTYPE html>
<html lang="">
<head>
    <title>Scrabblr - Add Game</title>
    <link rel="stylesheet" href="./static/style.css" />
</head>
<body>
    <form method="post">
        <div class="form-group">
            <label>
                First player:
                <select name="first_player">
                    <?php foreach ($playerNames as $name): ?>
                    <option value="<?=$name;?>"><?=$name;?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label>
                Match Date
                <input type="date" name="match_date" />
                <input type="time" name="match_time" />
            </label>
        </div>
        <div class="form-group">
            <label>
                Scores
                <textarea name="scores" rows="20"></textarea>
            </label>
        </div>
        <div class="form-group">
            <input type="submit" />
        </div>
    </form>
