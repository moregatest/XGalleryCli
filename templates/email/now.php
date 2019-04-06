<?php
/**
 * @var array  $list
 * @var string $category
 * @var string $query
 */
?>
<html>
<head>

</head>
<body>
<h2><?php echo count($list); ?> discounts on day</h2>
<strong><?php echo $category; ?></strong>
<p>
    <code><?php echo $query; ?></code>
</p>

<ul>
    <?php foreach ($list as $delivery): ?>
        <li><a
                    href="https://www.now.vn/ho-chi-minh/<?php echo $delivery->restaurant_url; ?>"><?php echo $delivery->name; ?></a>
        </li>
        <ul>
            <li>Address: <?php echo $delivery->address; ?></li>
            <li>Discount: <?php echo $delivery->discount; ?></li>
            <li>Min order: <?php echo $delivery->min_order_value; ?></li>
        </ul>
    <?php endforeach; ?>
</ul>
</body>
</html>