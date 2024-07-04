import { Router } from "express";
import { formatPhone, getSession, isExists } from "./whatsapp.js";
import sessionRouter from "./routes/session.js";
import messageRouter from "./routes/message.js";
import groupRouter from "./routes/group.js";
import response from "./response.js";

const router = Router();

router.use("/sessions", sessionRouter);
router.use("/messages", messageRouter);
router.use("/groups", groupRouter);

router.post("/check/:id", async (req, res) => {
    const session = getSession(req.params.id);
    const receiver = formatPhone(req.body.phone);

    try {
        const exists = await isExists(session, receiver);

        if (!exists) {
            return response(res, 404, false, "This phone number is not registered.");
        }

        response(res, 200, true, "This phone number is registered.");
    } catch {
        response(res, 500, false, "Failed to check the phone number.");
    }
});

router.all("*", (req, res) => {
    response(res, 404, false, "Not Allowed.");
});

export default router;
