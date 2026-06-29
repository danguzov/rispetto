/**
 * Kitchen order alert — warm two-note service chime (Web Audio API).
 * Requires a user gesture to unlock audio before first play.
 */

function getAudioContext(existing) {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;

    if (! AudioCtx) {
        return null;
    }

    const context = existing ?? new AudioCtx();

    if (context.state === 'suspended') {
        context.resume();
    }

    return context;
}

export function playKitchenOrderAlert(audioContext) {
    const context = getAudioContext(audioContext);

    if (! context) {
        return;
    }

    const start = context.currentTime;

    const playChime = (frequency, offset, peak, decay) => {
        const osc = context.createOscillator();
        const overtone = context.createOscillator();
        const filter = context.createBiquadFilter();
        const gain = context.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(frequency, start + offset);

        overtone.type = 'triangle';
        overtone.frequency.setValueAtTime(frequency * 2.02, start + offset);

        filter.type = 'lowpass';
        filter.frequency.setValueAtTime(3200, start + offset);
        filter.Q.setValueAtTime(0.8, start + offset);

        const attack = start + offset;
        gain.gain.setValueAtTime(0.0001, attack);
        gain.gain.exponentialRampToValueAtTime(peak, attack + 0.012);
        gain.gain.exponentialRampToValueAtTime(0.0001, attack + decay);

        osc.connect(filter);
        overtone.connect(filter);
        filter.connect(gain);
        gain.connect(context.destination);

        osc.start(attack);
        overtone.start(attack);
        osc.stop(attack + decay + 0.08);
        overtone.stop(attack + decay + 0.08);
    };

    // Bright service bell: E6 → B5 (restaurant / POS style)
    playChime(1318.51, 0, 0.22, 0.38);
    playChime(987.77, 0.14, 0.18, 0.52);
}

export function createKitchenAlarm() {
    return {
        sidebarOpen: false,
        alarmInterval: null,
        audioContext: null,
        audioUnlocked: false,

        unlockAudio() {
            if (! this.audioContext) {
                this.audioContext = getAudioContext(null);
            }

            if (this.audioContext?.state === 'suspended') {
                this.audioContext.resume();
            }

            this.audioUnlocked = true;
        },

        syncPendingAlarm(count) {
            const pending = Number(count) || 0;

            if (pending > 0) {
                if (! this.alarmInterval) {
                    this.playKitchenBeep();
                    this.alarmInterval = window.setInterval(() => this.playKitchenBeep(), 2000);
                }

                return;
            }

            this.stopPendingAlarm();
        },

        stopPendingAlarm() {
            if (this.alarmInterval) {
                window.clearInterval(this.alarmInterval);
                this.alarmInterval = null;
            }
        },

        playKitchenBeep() {
            if (! this.audioUnlocked) {
                return;
            }

            try {
                this.audioContext = getAudioContext(this.audioContext);
                playKitchenOrderAlert(this.audioContext);
            } catch (error) {
                console.warn('Zvuk nije dostupan.', error);
            }
        },
    };
}
