import { writable } from 'svelte/store';

export const messages = writable([]);

export function addMessage(message, type) {
    messages.update(val => {
        return [...val, {
            type,
            message
        }];
    });
}