<script>
    import { goto } from '$app/navigation';
	import { onMount } from "svelte";
    import { base } from '$app/paths'
    import { getConfig } from "$lib/api.js";
    import { messages } from "$lib/stores.js";

    export let data;

    async function validateSession() {
        console.log("validating session...")

        const cfg = await getConfig();
        const backendAddress = cfg.backendAddress;

        let response = await fetch(backendAddress + "/auth/validateSession.php", {
            method: "GET",
            credentials: "include",
        });

        let result = await response.json();

        if(result.error) {
            goto(base + "/auth/login");
        } 
        
        // else if(result.role == null || result.role != "admin") {
        //     goto(base + "/");
        // }
    }

    onMount(() => {
        validateSession();
    });
</script>

<div class="toast toast-end">
    {#each $messages as msg, i}
        <div class="alert alert-{msg.type}">
            <span>{msg.message}</span>
            <button class="btn btn-circle btn-ghost btn-xs" on:click={() => {
                messages.update(val => {
                    val.splice(i, 1);
                    return val;
                });
            }}>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    {/each}
</div>

<div class="flex h-screen">
    <nav class="bg-base-100 w-56 h-screen p-4 flex flex-col justify-between">
        <div>
            <a href="{base}" class="text-2xl text-center w-full block p-4">Own<span class="font-bold">Share</span></a>

            <ul class="menu p-0 w-full h-full">
                <li>
                    <a href="{base}/admin/files" class="w-full"><img src="{base}/icon/file.svg" alt="">Files</a>
                </li>
                <li>
                    <a href="{base}/admin/users"><img src="{base}/icon/users.svg" alt="">Users</a>
                </li>
                <li>
                    <a href="{base}/admin/settings"><img src="{base}/icon/settings.svg" alt="">Settings</a>
                </li>
            </ul>
        </div>

        <div>
            <a href="{base}/auth/logout" class="btn btn-outline btn-sm w-full">Logout</a>
        </div>
    </nav>

    <main class="p-8 w-full bg-base-300 h-auto overflow-y-scroll">
        <slot/>
    </main>
</div>