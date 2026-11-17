import { useCallback, useEffect, useRef, useState } from 'react';

import { hasHeardSpeech, listenStep, startListening, voiceLevel, type ListenState } from '@/Utils/kiosk';

// listening: the mic is on; unavailable: no mic (or a plain-HTTP page, where browsers keep it off); denied: not allowed
export type RecorderState = 'idle' | 'starting' | 'listening' | 'unavailable' | 'denied';

interface Options {
    // The question, recorded, once it's over
    onRecorded: (recording: Blob) => void;
    // Nothing was said, or the listening was stopped before anything was
    onNoSpeech: () => void;
}

// What the tablet can record: webm/opus in Chrome, mp4/aac in Safari
const MIME_TYPES = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4'];

// iOS routes sound to the earpiece while it's set up to record; these hint which one is wanted (Safari 16.4+)
export const setAudioSession = (type: 'play-and-record' | 'playback') => {
    const session = (navigator as Navigator & { audioSession?: { type: string } }).audioSession;
    if (session) session.type = type;
};

// Records one spoken question at a time, stopping by itself once the speaker pauses
export function useVoiceRecorder({ onRecorded, onNoSpeech }: Options) {
    const [state, setState] = useState<RecorderState>('idle');
    const [level, setLevel] = useState(0);
    const callbacks = useRef({ onRecorded, onNoSpeech });
    callbacks.current = { onRecorded, onNoSpeech };
    const session = useRef<{ finish: (keep: boolean) => void } | null>(null);

    const start = useCallback(async (context: AudioContext) => {
        session.current?.finish(false);

        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            setState('unavailable');
            return;
        }

        setState('starting');
        setAudioSession('play-and-record');

        let stream: MediaStream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true } });
        } catch (error) {
            setState(error instanceof DOMException && error.name === 'NotAllowedError' ? 'denied' : 'unavailable');
            return;
        }

        const mimeType = MIME_TYPES.find((type) => MediaRecorder.isTypeSupported(type));
        const recorder = new MediaRecorder(stream, mimeType ? { mimeType } : undefined);
        const chunks: Blob[] = [];
        const source = context.createMediaStreamSource(stream);
        const analyser = context.createAnalyser();
        analyser.fftSize = 1024;
        source.connect(analyser);
        const samples = new Float32Array(analyser.fftSize);

        let listen: ListenState = startListening(performance.now());
        let timer = 0;
        let finished = false;

        // keep: hand the recording on (when speech was heard); otherwise drop it quietly
        const finish = (keep: boolean) => {
            if (finished) return;
            finished = true;
            window.clearInterval(timer);
            session.current = null;

            const stopped = () => {
                // The mic goes off before anything plays, or iOS keeps playing through the earpiece
                stream.getTracks().forEach((track) => track.stop());
                source.disconnect();

                // Unless listening has started again in the meantime
                if (!session.current) {
                    setLevel(0);
                    setState('idle');
                }

                if (!keep) return;
                if (hasHeardSpeech(listen)) callbacks.current.onRecorded(new Blob(chunks, { type: recorder.mimeType || mimeType || 'audio/webm' }));
                else callbacks.current.onNoSpeech();
            };

            if (recorder.state === 'inactive') stopped();
            else {
                recorder.onstop = stopped;
                recorder.stop();
            }
        };

        recorder.ondataavailable = (event) => {
            if (event.data.size > 0) chunks.push(event.data);
        };
        recorder.start(250);
        session.current = { finish };
        setState('listening');

        timer = window.setInterval(() => {
            analyser.getFloatTimeDomainData(samples);
            const rms = Math.sqrt(samples.reduce((sum, sample) => sum + sample * sample, 0) / samples.length);
            const step = listenStep(listen, rms, performance.now());
            listen = step.state;
            setLevel(voiceLevel(rms, listen.noise));

            if (step.decision !== 'listen') finish(true);
        }, 50);
    }, []);

    // A tap while listening: the question is over (or dropped, if nothing was said yet)
    const stop = useCallback(() => session.current?.finish(true), []);

    // Leaving the page or cancelling: drop whatever was being recorded
    const cancel = useCallback(() => {
        session.current?.finish(false);
        setState('idle');
    }, []);

    useEffect(() => () => session.current?.finish(false), []);

    return { state, level, start, stop, cancel };
}
