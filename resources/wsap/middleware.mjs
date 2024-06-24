import { globalApiKey, rateLimitMax, rateLimitWindowMs } from "./config.mjs";
import { sendErrorResponse } from "./utils.mjs";
import { validateSession } from "./sessions.mjs";
import rateLimiting from "express-rate-limit";

const apikey = async (req, res, next) => {
    if (globalApiKey) {
        const apiKey = req.headers["x-api-key"];
        if (!apiKey || apiKey !== globalApiKey) {
            return sendErrorResponse(res, 403, "Invalid API key");
        }
    }
    next();
};

const sessionNameValidation = async (req, res, next) => {
    if (!/^[\w-]+$/.test(req.params.sessionId)) {
        return sendErrorResponse(
            res,
            422,
            "Session should be alphanumerical or -"
        );
    }
    next();
};

const sessionValidation = async (req, res, next) => {
    const validation = await validateSession(req.params.sessionId);
    if (validation.success !== true) {
        return sendErrorResponse(res, 404, validation.message);
    }
    next();
};

const rateLimiter = rateLimiting({
    max: rateLimitMax,
    windowMs: rateLimitWindowMs,
    message: "You can't make any more requests at the moment. Try again later",
});

export default {
    sessionValidation,
    apikey,
    sessionNameValidation,
    rateLimiter,
};
