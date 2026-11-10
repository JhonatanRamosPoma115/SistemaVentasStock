// Render the same artwork for the chooser and the Fabric layer.
export function drawVerseStamp(canvas, style, { verse, reference, ink, accent }) {
    canvas.width = 640;
    canvas.height = 420;
    const ctx = canvas.getContext('2d');
    const retro = style === 'retro';
    const script = style === 'caligrafico';
    const centered = retro || script;
    ctx.textAlign = centered ? 'center' : 'left';
    const x = centered ? 320 : 52;

    ctx.strokeStyle = accent;
    ctx.fillStyle = accent;
    ctx.lineWidth = retro ? 4 : 2;
    if (retro) {
        ctx.strokeRect(18, 18, 604, 384);
        ctx.strokeRect(29, 29, 582, 362);
    } else if (script) {
        ctx.beginPath();
        ctx.moveTo(184, 91);
        ctx.quadraticCurveTo(320, 50, 456, 91);
        ctx.moveTo(184, 329);
        ctx.quadraticCurveTo(320, 370, 456, 329);
        ctx.stroke();
    } else {
        ctx.fillRect(27, 73, 8, 267);
        ctx.fillRect(52, 346, 536, 4);
    }
    ctx.font = 'bold 24px sans-serif';
    ctx.fillText(retro ? '✦   PALABRA VIVA   ✦' : script ? '✧' : 'FE / PALABRA VIVA', x, 69);

    const width = 520;
    const words = verse.trim().split(/\s+/u).filter(Boolean);
    const family = retro ? 'bold Georgia, serif' : script ? 'italic "Segoe Script", "Brush Script MT", Georgia, serif' : '900 Impact, "Arial Black", sans-serif';
    const space = family.indexOf(' ');
    let lines;
    let size = script ? 58 : 64;
    for (; size >= 20; size -= 2) {
        ctx.font = `${family.slice(0, space)} ${size}px ${family.slice(space + 1)}`;
        lines = [];
        let line = '';
        for (const word of words) {
            const candidate = line ? `${line} ${word}` : word;
            if (line && ctx.measureText(candidate).width > width) {
                lines.push(line);
                line = word;
            } else {
                line = candidate;
            }
        }
        if (line) lines.push(line);
        if (lines.length * size * 1.12 <= 230) break;
    }
    ctx.fillStyle = ink;
    const lineHeight = size * 1.12;
    lines.forEach((line, i) => ctx.fillText(line, x, 216 + (i - (lines.length - 1) / 2) * lineHeight + size * .3, width));
    ctx.font = 'bold 24px sans-serif';
    ctx.fillStyle = accent;
    ctx.fillText(reference, x, 376, width);
    return canvas;
}
