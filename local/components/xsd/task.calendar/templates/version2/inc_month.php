<?php
$periodNext->modify('+1 month');
$periodPrev->modify('-1 month');

$periodStart = clone($periodDateTime);
$periodStart->modify("first day of");
$periodMonthStart = clone($periodStart);

$periodEnd = clone($periodStart);
$periodEnd->modify("+1 month");

$periodStart->modify("this week");
?>

<?php $periodStart->format("Y/m/d"); ?>
<?php $periodEnd->format("Y/m/d"); ?>

