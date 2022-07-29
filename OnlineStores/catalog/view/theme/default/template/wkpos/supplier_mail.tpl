<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $subject; ?></title>
</head>
<body> 
<div class="content">
	Dear <?php echo $supplier_name; ?>,
	<br /><br />
	Please send the below listed product(s) as per the given quantity as soon as possible.
	<br /><br />
	<table border="1" cellpadding="10" cellspacing="0" style="text-align: center;">
		<thead style="font-weight: bold;">
			<tr>
				<td>Product Name</td>
				<td>Quantity</td>
				<td>Comment</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $product) { ?>
			<tr>
				<td><?php echo $product['name']; ?></td>
				<td><?php echo $product['quantity']; ?></td>
				<td><?php echo $product['comment']; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<br /><br />
	Regards
	<br />
	<?php echo $user; ?><br />
	<?php echo $outlet; ?><br />
	<?php echo $address; ?>
</div>
</body>
</html>
