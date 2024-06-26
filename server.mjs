// index.js

import app from "./resources/wsap/app.mjs";
import { serverPort, baseWebhookURL } from "./resources/wsap/config.mjs";

// Check if BASE_WEBHOOK_URL environment variable is available
if (!baseWebhookURL) {
    console.error(
        "BASE_WEBHOOK_URL environment variable is not available. Exiting..."
    );
    process.exit(1); // Terminate the application with an error code
}

app.listen(serverPort, () => {
    console.log(`Server running on port ${serverPort}`);
});
