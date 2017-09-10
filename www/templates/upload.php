<div class="box">
<?php if ($delete_id): ?>
  <p>Your delete file id <?=$id?></p>
<?php endif ?>
<?php if (!isset($authUrl)): ?>
  <p><a href="?logout">logout</a></p>
<?php endif?>
<?php if (isset($authUrl)): ?>
  <div class="request">
    <a class='login' href='<?= $authUrl ?>'>Connect Me!</a>
  </div>
<?php elseif($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
  <div class="shortened">
    <p>Your call was successful! Check your drive for the following files:</p>

    <ul>
      <li><a href="https://drive.google.com/open?id=<?= $result->id ?>" target="_blank"><?= $result->name ?></a></li>
    </ul>
  </div>

<?php else: ?>

  <form action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data" style="border:1px solid">
    <p><b>Upload new file!</b></p>
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    <input type="file" name="userfile"><br> 
    <input type="submit" value="Upload"><br>
  </form>
  <table>
    <?php foreach ($files_list as $key => $result):?>
      <tr>
        <td><a href="https://drive.google.com/open?id=<?= $result->id ?>" target="_blank"><?= $result->name ?></a></td>
        <td><a href="<?=$_SERVER['PHP_SELF']?>?delete=<?= $result->id ?>">delete</a></td>
      </tr>
    <?php endforeach ?>
  </table>
<?php endif ?>
</div>