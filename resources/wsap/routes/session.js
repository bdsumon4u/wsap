import { Router } from "express";
import { body } from "express-validator";
import requestValidator from "../middlewares/requestValidator.js";
import sessionValidator from "../middlewares/sessionValidator.js";
import * as controller from "../controllers/sessionController.js";

const router = Router();

router.get("/get/:id", sessionValidator, controller.getxscode);

router.get("/status/:id", sessionValidator, controller.status);

router.post(
    "/create",
    body("id").notEmpty(),
    requestValidator,
    controller.create
);

router.delete("/delete/:id", sessionValidator, controller.destroy);

export default router;
