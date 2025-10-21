let currentDialog = null;

export function resetDialogState() {
    currentDialog = null;
}

export function showDialog(k, message) {
    closeDialog(k);

    currentDialog = k.add([
        k.pos(30, k.height() - 130),
        k.fixed(),
        k.z(100),
        "dialogBox"
    ]);

    currentDialog.add([
        k.rect(k.width() - 60, 100, { radius: 8 }),
        k.color(30, 20, 10),
        k.outline(2, k.rgb(192, 160, 96)),
    ]);

    currentDialog.add([
        k.pos(15, 15),
        k.text(message, {
            size: 20,
            width: k.width() - 90,
            font: "Arial, sans-serif",
        }),
        k.color(255, 240, 220),
    ]);
}

export function closeDialog(k) {
    k.destroyAll("dialogBox");
    currentDialog = null;
}