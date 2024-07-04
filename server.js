import "dotenv/config";
import cors from "cors";
import express from "express";
import expressListRoutes from "express-list-routes";
import nodeCleanup from "node-cleanup";
import routes from "./resources/wsap/routes.js";
import { init, cleanup } from "./resources/wsap/whatsapp.js";

const app = express();
const host = process.env.WSAP_HOST || undefined;
const port = parseInt(process.env.WSAP_PORT ?? 3000);

app.use(cors());
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use("/", routes);

expressListRoutes(routes);

const listenerCallback = () => {
    init();
    console.log(
        `Server is listening on http://${host ? host : "localhost"}:${port}`
    );
};

if (host) {
    app.listen(port, host, listenerCallback);
} else {
    app.listen(port, listenerCallback);
}

nodeCleanup(cleanup);

export default app;
