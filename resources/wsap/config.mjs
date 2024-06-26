// config.js

import dotenv from "dotenv";

// Load environment variables from .env file
dotenv.config();

// setup global const
const pupExecutablePath =
    process.env.PUPPETEER_EXECUTABLE_PATH || "/usr/bin/chromium-browser";
const sessionFolderPath =
    process.env.SESSIONS_PATH || "./storage/framework/sessions/wsap";
const enableLocalCallbackExample =
    (process.env.ENABLE_LOCAL_CALLBACK_EXAMPLE || "").toLowerCase() === "true";
const globalApiKey = process.env.API_KEY || "";
const serverPort = process.env.PORT || 3000;
const baseWebhookURL =
    process.env.BASE_WEBHOOK_URL || "http://localhost:3000/localCallbackExample";
const maxAttachmentSize = parseInt(process.env.MAX_ATTACHMENT_SIZE) || 10000000;
const setMessagesAsSeen =
    (process.env.SET_MESSAGES_AS_SEEN || "").toLowerCase() === "true";
const disabledCallbacks = process.env.DISABLED_CALLBACKS
    ? process.env.DISABLED_CALLBACKS.split("|")
    : [];
const enableSwaggerEndpoint =
    (process.env.ENABLE_SWAGGER_ENDPOINT || "").toLowerCase() === "true";
const webVersion = process.env.WEB_VERSION || "2.2328.5";
const webVersionCacheType = process.env.WEB_VERSION_CACHE_TYPE || "none";
const rateLimitMax = process.env.RATE_LIMIT_MAX || 1000;
const rateLimitWindowMs = process.env.RATE_LIMIT_WINDOW_MS || 1000;
const recoverSessions =
    (process.env.RECOVER_SESSIONS || "").toLowerCase() === "true";

export {
    pupExecutablePath,
    sessionFolderPath,
    enableLocalCallbackExample,
    globalApiKey,
    serverPort,
    baseWebhookURL,
    maxAttachmentSize,
    setMessagesAsSeen,
    disabledCallbacks,
    enableSwaggerEndpoint,
    webVersion,
    webVersionCacheType,
    rateLimitMax,
    rateLimitWindowMs,
    recoverSessions,
};
