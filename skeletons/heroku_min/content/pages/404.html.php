<h2 style="color: red;">404: Not Found</h2>

<?php
use alkemann\h2l\Environment;

if (Environment::current() == Environment::DEV || Environment::current() == Environment::LOCAL): // dev mode

?>
<p>View file not found!</p>
<p>Create it at: <strong style="color:blue"> <?=$message?> </strong></p>
<?php
endif;
?>
