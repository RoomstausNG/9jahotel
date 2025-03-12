function startInactivityTimer() {
    // Set the time interval for inactivity (in milliseconds)
    const inactivityTimeout = 15 * 60 * 1000; // 5 minutes

    let inactivityTimer;

    function resetTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            // Refresh the page
            window.location.reload(true); // Force reload from the server

            // Clear cookies (replace 'cookieName' with the name of your cookie)
            document.cookie =
                "cookieName=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }, inactivityTimeout);
    }

    // Reset the timer on user activity
    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);

    // Start the timer when the page loads
    resetTimer();
}

startInactivityTimer();
