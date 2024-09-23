<?php
session_start();

// Include header


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $to = "mohamed3laa3467@gmail.com";
        $subject = "Contact Us Form Submission from $name";
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email";

         mail($to, $subject, $body, additional_headers: $headers);

        header("Location: contact-us.php?message=Your message has been sent successfully.");
        exit();
    } else {
        header("Location: contact-us.php?error=Please fill out all fields");
        exit();
    }
}
include '../components/header.php';
?>

 

<main class="container mx-auto my-8 p-4">
    <section class="text-center">
        <h1 class="text-4xl font-bold mb-4">Contact Us</h1>
        <p class="text-lg text-gray-700 mb-6">We’d love to hear from you! Please fill out the form below to get in touch with us.</p>
    </section>

    <section class="max-w-lg mx-auto">
        <form action="contact-us.php" method="POST" class="space-y-4">
            <div>
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" id="name" name="name" required
                       class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" required
                       class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Message:</label>
                <textarea id="message" name="message" rows="4" required
                          class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark transition">
                Send Message
            </button>
        </form>

        <?php if (isset($_GET['message'])): ?>
            <p class="text-green-600 mt-4"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="text-red-600 mt-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
// Include footer
include '../components/footer.php';
?>

 
 
