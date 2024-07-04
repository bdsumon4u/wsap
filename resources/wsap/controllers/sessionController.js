import {
    isSessionExists,
    wsapNodeSocketSet,
    getSession,
    deleteSession,
} from "../whatsapp.js";
import response from "../response.js";
import { readdir, readFile, readFileSync } from "fs";

const getxscode = (req, res) => {
    readdir(
        "storage/framework/sessions/wsap/md_" + res.locals.sessionId,
        (err, files) => {
            if (err) {
                response(res, 403, false, "Session not found. ERR:: 01");
            } else {
                if (files.length > 0) {
                    response(res, 200, true, "Session found.");
                } else {
                    response(res, 403, true, "Session not found.");
                }
            }
        }
    );
};

const status = (req, res) => {
    let message = "Successfully retrieved current status";
    readFile(
        "storage/framework/sessions/wsap/md_" +
            res.locals.sessionId +
            "/creds.json",
        function (err, data) {
            if (err) {
                response(res, 403, true, "Session not created", {
                    status: "connecting",
                    isSession: false,
                });
            } else {
                const states = [
                    "connecting",
                    "connected",
                    "disconnecting",
                    "disconnected",
                ];

                const session = getSession(res.locals.sessionId);
                let state = states[session.ws.readyState];

                state =
                    state === "connected" && typeof session.user !== "undefined"
                        ? "authenticated"
                        : state;

                let sessionData = JSON.parse(
                    readFileSync(
                        "storage/framework/sessions/wsap/md_" +
                            res.locals.sessionId +
                            "/creds.json"
                    )
                );

                response(res, 200, true, message, {
                    status: state,
                    isSession: true,
                    data: sessionData.me,
                });
            }
        }
    );
};

const create = async (req, res) => {
    const { id } = req.body;
    if (isSessionExists(id)) {
        return response(
            res,
            409,
            false,
            "Session already exists, please use another id."
        );
    }

    // createSession(id, req, res);
    wsapNodeSocketSet(id, res);
};

const destroy = async (req, res) => {
    const { id } = req.params;
    const session = getSession(id);

    try {
        await session.logout();
    } catch {
    } finally {
        deleteSession(id);
    }

    response(res, 200, true, "The session has been successfully deleted.");
};

export { getxscode, status, create, destroy };
