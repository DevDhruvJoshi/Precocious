<!DOCTYPE html>
<html>
    <head>
        <title>Dashboard</title>
    <body>
        <h1>Welcome to Your Dashboard</h1>
        <h1>User Name <?php echo isset($_SESSION['User']['Name']) ? $_SESSION['User']['Name'] : 'No Login'; ?></h1>

        <div class="dashboard-content">
            <p>This is where you can display user-specific information and actions.</p>
        </div>

        <?php if( isset($_SESSION['User']['Name']) ){ ?>
        <a href="/logout" class="logout-button">Logout</a>
        <?php } else { ?>
            <a href="/login" class="logout-button">Login</a>
        <?php } ?>
    </body>
</html>