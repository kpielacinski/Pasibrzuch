<?php include('partials-front/menu.php');

$pdo = pdo_connect_mysql();

// If the user clicked the add to cart button on the product page we can check for the form data
if (isset($_POST['id'], $_POST['quantity']) && is_numeric($_POST['id']) && is_numeric($_POST['quantity'])) {
    // Set the post variables so we easily identify them, also make sure they are integer
    $id = (int)$_POST['id'];
    $quantity = (int)$_POST['quantity'];
    // Prepare the SQL statement, we basically are checking if the product exists in our databaser
    $stmt = $pdo->prepare('SELECT * FROM tbl_food WHERE id = ?');
    $stmt->execute([$_POST['id']]);
    // Fetch the product from the database and return the result as an Array
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if the product exists (array is not empty)
    if ($product && $quantity > 0) {
        // Product exists in database, now we can create/update the session variable for the cart
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            if (array_key_exists($id, $_SESSION['cart'])) {
                // Product exists in cart so just update the quanity
                $_SESSION['cart'][$id] += $quantity;
            } else {
                // Product is not in cart so add it
                $_SESSION['cart'][$id] = $quantity;
            }
        } else {
            // There are no products in cart, this will add the first product to cart
            $_SESSION['cart'] = array($id => $quantity);
        }
    }

    // Prevent form resubmission...
    header('location: cart.php');
    exit;
}

if (isset($_POST['remove']) && is_numeric($_POST['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_POST['remove']])) {
    // Remove the product from the shopping cart
    unset($_SESSION['cart'][$_POST['remove']]);
}

if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    // Loop through the post data so we can update the quantities for every product in cart
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;
            // Always do checks and validation
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                // Update new quantity
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }
    // Prevent form resubmission...
    header('location: cart.php');
    exit;
}

if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();
$subtotal = 0.00;
// If there are products in cart
if ($products_in_cart) {
    // There are products in the cart so we need to select those products from the database
    // Products in cart array to question mark string array, we need the SQL statement to include IN (?,?,?,...etc)
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT * FROM tbl_food WHERE id IN (' . $array_to_question_marks . ')');
    // We only need the array keys, not the values, the keys are the id's of the products
    $stmt->execute(array_keys($products_in_cart));
    // Fetch the products from the database and return the result as an Array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Calculate the subtotal
    foreach ($products as $product) {
        $subtotal += (float)$product['price'] * (int)$products_in_cart[$product['id']];
    }
}
?>

<div class="cart content-wrapper">
    <h1>Shopping Cart</h1>
    <form action="cart.php" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Product</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">You have no products added in your Shopping Cart</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="img">
                            <img src="images/food/<?=$product['image_name']?>" width="50" height="50" alt="<?=$product['title']?>">
                    </td>
                    <td>
                        <a href="cart.php"><?=$product['title']?></a>
                        <br>
                        <form action="cart.php" method="post">
                                    <input type="hidden" name="remove" value="<?=$product['id']?>"/>
                                    <input class="btn btn-primary" type="submit" value="Remove">
                                </form>
                    </td>
                    <td class="price">&dollar;<?=$product['price']?></td>
                    <td class="quantity">
                        <input type="number" name="quantity-<?=$product['id']?>" value="<?=$products_in_cart[$product['id']]?>" min="1" placeholder="Quantity" required>
                    </td>
                    <td class="price">&dollar;<?=$product['price'] * $products_in_cart[$product['id']]?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="subtotal">
            <span class="text">Subtotal</span>
            <span class="price">&dollar;<?=$subtotal?></span>
        </div>
        <div class="buttons">
            <input type="submit" value="Update" name="update">
            <a href="placeorder.php">
            <input type="button" value="Place Order" name="placeorder">
            </a>
        </div>
    </form>
</div>

