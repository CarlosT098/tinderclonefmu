<h2>Seus Matches</h2>
<ul>
  <?php foreach ($matches as $match): ?>
    <li>
      <img src="/api/assets/images/<?= $match['foto'] ?>" width="50" />
      <?= $match['nome'] ?> - <a href="/mensagens?user=<?= $match['id'] ?>">Conversar</a>
    </li>
  <?php endforeach; ?>
</ul>
