import { rmSync, readdir } from "fs";
import { join } from "path";
import pino from "pino";
import makeWASocket, {
    useMultiFileAuthState,
    makeInMemoryStore,
    Browsers,
    DisconnectReason,
    delay,
} from "@whiskeysockets/baileys";
import { toDataURL } from "qrcode";
import __dirname from "./dirname.js";
import response from "./response.js";

const sessions = new Map();
const retries = new Map();

const sessionDir = (sessionFile = "") => {
    return join(
        __dirname,
        "storage/framework/sessions/wsap",
        sessionFile ? sessionFile : ""
    );
};

const isSessionExists = (sessionId) => {
    return sessions.has(sessionId);
};

const shouldReconnect = (sessionId) => {
    let maxRetries = parseInt(process.env.MAX_RETRIES ?? 0);
    let attempts = retries.get(sessionId) ?? 0;

    maxRetries = maxRetries < 1 ? 1 : maxRetries;

    if (attempts < maxRetries) {
        retries.set(sessionId, ++attempts);

        return true;
    }

    return false;
};

// proxy security && system logic

const wsapNodeSocketSet = async (sessionId, res = null) => {
    try {
        const sessionFile = `md_${sessionId}`;
        const logger = pino({ level: "warn" });
        const store = makeInMemoryStore({ logger });
        let { state, saveCreds: saveState } = await useMultiFileAuthState(
            sessionDir(sessionFile)
        );

        /**
         * @type {import('@adiwajshing/baileys').CommonSocketConfig}
         */
        const waConfig = {
            auth: state,
            printQRInTerminal: true,
            logger,
            browser: Browsers.macOS("Mac OS"),
        };

        /**
         * @type {import('@adiwajshing/baileys').AnyWASocket}
         */
        const wa = makeWASocket.default(waConfig);

        store.readFromFile(sessionDir(`${sessionId}_store.json`));
        store.bind(wa.ev);

        sessions.set(sessionId, { ...wa, store });

        wa.ev.on("creds.update", saveState);

        wa.ev.on("chats.set", ({ chats }) => {
            //
        });

        wa.ev.on("connection.update", async (update) => {
            const { connection, lastDisconnect } = update;
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            if (connection === "open") {
                retries.delete(sessionId);
            }
            if (connection === "close") {
                if (
                    statusCode === DisconnectReason.loggedOut ||
                    !shouldReconnect(sessionId)
                ) {
                    if (res && !res.headersSent) {
                        response(res, 400, false, "Unable to create session.");
                    }
                    return deleteSession(sessionId);
                }
                setTimeout(
                    () => {
                        wsapNodeSocketSet(sessionId, res);
                    },
                    statusCode === DisconnectReason.restartRequired
                        ? 0
                        : parseInt(process.env.RECONNECT_INTERVAL ?? 0)
                );
            }
            if (update.qr) {
                if (res && !res.headersSent) {
                    try {
                        const qr = await toDataURL(update.qr);
                        response(
                            res,
                            200,
                            true,
                            "QR code received, please scan the QR code.",
                            { qr }
                        );
                        return;
                    } catch {
                        response(res, 400, false, "Unable to create QR code.");
                    }
                }
                try {
                    await wa.logout();
                } catch {
                } finally {
                    deleteSession(sessionId);
                }
            }
        });
    } catch (error) {
        if (res) {
            res.status(404).json({ error: "An error occurred" });
        }
    }
};

const createSession = async (sessionId, req, res = null) => {
    try {
        const waConfig = {
            auth: state,
            printQRInTerminal: true,
            logger: false,
            browser: Browsers.macOS("Chrome"),
        };
        const wa = makeWASocket.default(waConfig);

        store.readFromFile(sessionDir(`${sessionId}_store.json`));
        store.bind(wa.ev);

        sessions.set(sessionId, { ...wa, store });

        wa.ev.on("creds.update", saveState);

        wa.ev.on("chats.set", ({ chats }) => {
            //
        });
    } catch (error) {
        if (res) {
            res.status(400).json({ error: "An error occurred" });
        }
    }
};

const getSession = (sessionId) => {
    return sessions.get(sessionId) ?? null;
};

const deleteSession = (sessionId) => {
    const sessionFile = `md_${sessionId}`;
    const storeFile = `${sessionId}_store.json`;
    const rmOptions = { force: true, recursive: true };

    rmSync(sessionDir(sessionFile), rmOptions);
    rmSync(sessionDir(storeFile), rmOptions);

    sessions.delete(sessionId);
    retries.delete(sessionId);
};

const getChatList = (sessionId, isGroup = false) => {
    const filter = isGroup ? "@g.us" : "@s.whatsapp.net";

    return getSession(sessionId).store.chats.filter((chat) => {
        return chat.id.endsWith(filter);
    });
};

const isExists = async (session, jid, isGroup = false) => {
    try {
        let result;

        if (isGroup) {
            result = await session.groupMetadata(jid);

            return Boolean(result.id);
        }

        [result] = await session.onWhatsApp(jid);

        return result.exists;
    } catch {
        return false;
    }
};

const sendMessage = async (session, receiver, message, delayMs = 2000) => {
    try {
        await delay(parseInt(delayMs));

        return session.sendMessage(receiver, message);
    } catch {
        return Promise.reject(null); // eslint-disable-line prefer-promise-reject-errors
    }
};

const formatPhone = (phone) => {
    if (phone.endsWith("@s.whatsapp.net")) {
        return phone;
    }

    return phone.replace(/\D/g, "") + "@s.whatsapp.net";
};

const formatGroup = (group) => {
    if (group.endsWith("@g.us")) {
        return group;
    }

    return group.replace(/[^\d-]/g, "") + "@g.us";
};

const cleanup = () => {
    console.log("Running cleanup before exit.");
    sessions.forEach((session, sessionId) => {
        session.store.writeToFile(sessionDir(`${sessionId}_store.json`));
    });
};

const init = () => {
    readdir(sessionDir(), (err, files) => {
        if (err) {
            throw err;
        }
        for (const file of files) {
            if (!file.startsWith("md_") || file.endsWith("_store")) {
                continue;
            }
            wsapNodeSocketSet(file.replace(".json", "").substring(3));
        }
    });
};

export {
    isSessionExists,
    wsapNodeSocketSet,
    createSession,
    getSession,
    deleteSession,
    getChatList,
    isExists,
    sendMessage,
    formatPhone,
    formatGroup,
    cleanup,
    init,
};
