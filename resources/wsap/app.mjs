import routes from "./routes.mjs";
import { restoreSessions } from "./sessions.mjs";
import express from "express";
import bodyParser from "body-parser";
import { maxAttachmentSize } from "./config.mjs";

const app = express();

// Initialize Express app
app.disable("x-powered-by");
app.use(bodyParser.json({ limit: maxAttachmentSize + 1000000 }));
app.use(
    bodyParser.urlencoded({
        limit: maxAttachmentSize + 1000000,
        extended: true,
    })
);
app.use("/", routes);

restoreSessions();

export default app;
