<script>
    import { goto } from '$app/navigation';
	import { onMount } from "svelte";
    import { getOption, setOption } from "$lib/api.js";
    import { addMessage } from "$lib/stores.js"; 

    let pageError = "";

    let loading = true;

    let options = {
        files_upload_folder_location: "",
        files_default_save_time: "",
        files_id_length: "",
        files_upload_chunk_size: "",
    }

    async function fetchOptions() {
        for(let option in options) {
            options[option] = await getOption(option);
        }
    }

    async function saveOptions() {
        let success = true;

        for(let option in options) {
            let result = await setOption(option, options[option]);
            if(result.error != null) {
                console.log("error while setting option: " + name);
                success = false;
            }
        }

        if(success) {
            addMessage("Options saved!", "success");        
        } else {
            addMessage("Error while saving options!", "error");        
        }

        fetchOptions();
    }

    onMount(async () => {
        await fetchOptions();
        loading = false;
    })

</script>

<h1 class="text-4xl font-bold">Settings</h1>

<span class="mb-4 text-red-500">{pageError}</span>

<!-- <h2 class="text-2xl font-bold">General Settings</h2> -->

<br>

<h2 class="text-2xl font-bold">File Settings</h2>

{#if loading}
    <div class="w-full flex justify-center items-center">
        <span class="loading loading-dots loading-lg"></span>
    </div>
{:else}
    <label class="form-control w-full">
        <div class="label"><span class="label-text">Upload Folder Location</span></div>
        <input type="text" class="input input-bordered w-full max-w-lg" bind:value={options.files_upload_folder_location}/>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Default save time</span></div>
        <input type="text" class="input input-bordered w-full max-w-lg" bind:value={options.files_default_save_time}/>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Id length</span></div>
        <input type="text" class="input input-bordered w-full max-w-lg" bind:value={options.files_id_length}/>
    </label>

    <label class="form-control w-full">
        <div class="label"><span class="label-text">Upload Chunk Size</span></div>
        <input type="text" class="input input-bordered w-full max-w-lg" bind:value={options.files_upload_chunk_size}/>
    </label>

    <button class="btn btn-priary mt-8" on:click={saveOptions}>Save</button>
{/if}

