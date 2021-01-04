<?php
require_once __DIR__ . '/autoload.php';

$matches = query('Select id, match_date From matches');
$totals = query('Select name, wins, max_score, min_score, avg_score from summary Order by name');
$crossTab = query('select first_player, winner, count(*) as count from matches m join winners w on m.id = w.match_id group by 1, 2');

$headlines = [
    'total' => [
        'line' => 'Matches: {count}',
        'query' => query('select count(*) as count from matches'),
    ],
    'turns' => [
        'line' => 'Turns: {count}',
        'query' => query('select count(*) as count from scores'),
    ],
    'avg_total' => [
        'line' => 'Average Total: {avg}',
        'query' => query('select avg(winner_score + loser_score) as avg from winners'),
    ],
    'busiest_day' => [
        'line' => 'Most games in a day: {count} ({date})',
        'query' => query('select date(match_date) as `date`, count(*) as count from matches m where match_date is not null group by 1 order by 2 desc limit 1'),
    ],
    'best_score' => [
        'line' => 'Best score: {score} ({player_name})',
        'query' => query('select player_name, score from scores order by 2 desc limit 1'),
    ]
];

$winners = [];

foreach ($crossTab as $row) {
    $winners[$row['first_player']][$row['winner']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="">
<head>
    <title>Scrabblr</title>
    <link rel="stylesheet" href="./static/style.css" />
</head>
<body>
    <!-- <img src="./static/tile.jpg" style="position: absolute; right: 20%; top: 1em;" alt="What a lovely tile" /> -->
    <h1>Summary</h1>
    <?php
        foreach ($headlines as $headline):
            $results = iterator_to_array($headline['query']);
    ?>
        <p><?=
            preg_replace_callback('/{([^}]+?)}/', function ($match) use ($results) {
                return $results[0][$match[1]];
            }, $headline['line']);; ?></p>
    <?php endforeach; ?>
    <table>
        <tr>
            <th>Player</th>
            <th>Wins</th>
            <th>Highest</th>
            <th>Lowest</th>
            <th>Average</th>
        </tr>
        <?php foreach ($totals as $total): ?>
        <tr>
            <td><?= $total['name']; ?></td>
            <td><?= $total['wins']; ?></td>
            <td><?= $total['max_score']; ?></td>
            <td><?= $total['min_score']; ?></td>
            <td><?= sprintf('%.2f', $total['avg_score']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <table>
        <tr>
            <th></th>
            <th>Han Wins</th>
            <th>Iain Wins</th>
        </tr>

        <tr>
            <th>Han First</th>
            <td><?= $winners['Han']['Han']; ?></td>
            <td><?= $winners['Han']['Iain']; ?></td>
        </tr>
        <tr>
            <th>Iain First</th>
            <td><?= $winners['Iain']['Han']; ?></td>
            <td><?= $winners['Iain']['Iain']; ?></td>
        </tr>
    </table>
    <!--
    <h1>View Match</h1>
    <form action="./match" method="get">
        <label>
            <select name="id">
                <?php foreach ($matches as $match): ?>
                <option value="<?= $match['id'] ?>">Match <?= $match['id'] ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <input type="submit" value="Go" />
    </form>
    -->

    <h1>Actions</h1>
    <ul>
        <li>
            <a href="./add">Add new match</a>
        <li>
            Stats
            <ul>
                <li><a href="./history/?option=scores">All scores</a>
                <li><a href="./history/?option=average">Average score</a>
                <li><a href="./history/?option=rolling">Ten game rolling average</a>
                <li><a href="./history/?option=optimum">Best turns</a>
                <li><a href="./history/?option=total">Overall total scores</a>
    </ul>