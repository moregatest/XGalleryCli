<?php
/**
 * @var array $data
 */
$list       = $data['list'];
$categories = $data['categories'];
?>
<html>
<head>

</head>
<body>
<h2>Discounts on day</h2>
<strong><?php echo $categories; ?></strong>

<?php foreach ($list as $group => $items): ?>
    <h3><?php echo $group; ?></h3>
    <?php foreach ($items as $delivery): ?>
        <ul>
            <li>
                <a href="https://www.now.vn/ho-chi-minh/<?php echo $delivery->restaurant_url; ?>"><?php echo $delivery->name; ?></a>
            </li>
        </ul>
        <ul>
            <li>Address: <?php echo $delivery->address; ?></li>
            <li>Discount: <?php echo $delivery->discount; ?></li>
            <li>Min order: <?php echo $delivery->min_order_value; ?></li>
        </ul>
    <?php endforeach; ?>
<?php endforeach; ?>

</body>
</html>