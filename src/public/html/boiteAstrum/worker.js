let timerID = null;
let interval = 25;

function tick() {
    postMessage("tick");
    // Schedule the next tick
    if (timerID) {
        timerID = setTimeout(tick, interval);
    }
}

self.onmessage = function(e) {
    if (e.data === "start") {
        if (!timerID) {
            timerID = setTimeout(tick, interval);
        }
    } else if (e.data.interval) {
        interval = e.data.interval;
        if (timerID) {
            clearTimeout(timerID);
            timerID = setTimeout(tick, interval);
        }
    } else if (e.data === "stop") {
        if (timerID) {
            clearTimeout(timerID);
            timerID = null;
        }
    }
};
