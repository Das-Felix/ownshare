<script>
    import Modal from "./Modal.svelte";

    import { getOption, setOption } from "$lib/api.js";
    import { createEventDispatcher } from 'svelte';
    import { getConfig } from "$lib/api.js";
    import { base } from '$app/paths'

    const dispatch = createEventDispatcher();

    let files = [];
    let error = "";
    let currentView = "CHOOSE_FILES";

    let chunkSize = 0;


    //Upload Form Data
    let title = "";
    let comment = "";
    let password = "";
    let maxDownloads = "";
    let saveDuration = "";

    //Collection Info
    let collectionInfo = "";

    function handleFileChange(event) {
        const selectedFiles = Array.from(event.target.files);
        files = [...files, ...selectedFiles];
        event.target.value = '';
    }

    async function startUpload() {
        chunkSize = parseInt(await getOption("files_upload_chunk_size") * 8);
        const cfg = await getConfig();
        
        for(let i = 0; i < files.length; i++) {
            files[i].neededChunks = Math.ceil(files[i].size / chunkSize);
            files[i].progress = 0;
            files[i].lastChunkTime = 0;
            console.log(files[i].neededChunks)
        }
    
        currentView = "UPLOADING";
        let sessionData = await createUploadSession(cfg);
        await uploadFiles(sessionData.collection_id, cfg);
        await finishUpload(sessionData.collection_id, cfg);
        collectionInfo = sessionData;
    }

    async function uploadFiles(sessionId, cfg) {
        for(let i = 0; i < files.length; i++) {
            await uploadFile(i, sessionId, cfg);   
        }
    }

    async function createUploadSession(cfg) {
        const backendAddress = cfg.backendAddress;

        let fd = new FormData();
        fd.set("title", title);
        fd.set("comment", comment);
        fd.set("password", password);
        fd.set("max_downloads", maxDownloads);
        fd.set("save_duration", saveDuration);

        let response = await fetch(backendAddress + "/admin/files/upload.php", {
            method: "POST",
            credentials: "include",
            body: fd
        });

        let result = await response.json();
        return result;
    }

    async function uploadFile(fileIndex, sessionId, cfg) {
        const backendAddress = cfg.backendAddress;
        let file = files[fileIndex];
        for (let start = 0; start < file.size; start += chunkSize) {
            const startTime = Date.now();
            const chunk = file.slice(start, start + chunkSize)
            const fd = new FormData();
            fd.set('data', chunk);
            fd.set('name', file.name);
            fd.set('collection', sessionId);
            fd.set('size', file.size);

            let response = await fetch(backendAddress + "/admin/files/uploadChunk.php", {
                method: "POST",
                credentials: "include",
                body: fd
            });

            //TODO: proper time estimation
            const secSpent = (Date.now() - startTime) / 1000;
            files[fileIndex].progress = start/chunkSize + 1;
            files[fileIndex].timeEst = (files[fileIndex].neededChunks - files[fileIndex].progress) * secSpent;

            let result = await response.json();

            console.log(result)
        }

    }

    async function finishUpload(sessionId, cfg) {
        const backendAddress = cfg.backendAddress;
        let fd = new FormData();
        fd.set("collection_id", sessionId);

        let response = await fetch(backendAddress + "/admin/files/finishUpload.php", {
            method: "POST",
            credentials: "include",
            body: fd
        });

        let result = await response.text();
        console.log(result);
        // if(result.error != null) {
        //     console.log("Error: ", result.error);
        // }

        console.log("upload finished!");
        dispatch("uploadFinished", {});
        currentView = "FINISHED";
    }

    function truncate(str, n){
        var split = str.split('.');
        var filename = split[0];
        var extension = split[1];
        if (filename.length > n) {
            filename = filename.substring(0, n);
        }
        var result = filename + '...' + extension;
        return result;
    };

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function removeFile(index) {
        files = files.filter((_, i) => i !== index);
    }

    function reset() {
        files = [];
        title = "";
        comment = "";
        password = "";
        maxDownloads = "";
        saveDuration = "";
        currentView = "CHOOSE_FILES";        
    }

    $: {
        if(showUploadModal == true) {
            reset();
        }
    }


    export let showUploadModal = false;
</script>

<Modal bind:showModal={showUploadModal}>
    <h2 class="text-2xl font-bold mb-4">Upload Files</h2>

    <span class="text-red-300">{error}</span>

    <div class="views overflow-hidden h-full relative">

        <!-- FINISHED -->
        {#if currentView == "FINISHED"}
            <div class="gap-8 h-ful absolute top-0 left-0 w-full h-full z-10 flex items-center justify-center">
                <img class="w-12" src="/icon/upload_finished.svg" alt="">
                <h3 class="font-bold text-4xl">Upload Finished</h3>
            </div>
        {/if}

        <!-- UPLOADING -->
        {#if currentView == "UPLOADING"}
            <div class="flex gap-8 h-ful absolute top-0 left-0 w-full h-full z-10">
                <div class="flex flex-col gap-2 max-h-full overflow-y-scroll w-full">
                    {#if files != null}
                        {#each files as file, index}
                            <div class="p-4 border-solid border-2 border-base-300 rounded-lg">
                                <div class="flex items-center justify-between gap-2">
                                    <span>{truncate(file.name, 25)}</span>
                                    <div class="flex gap-1">
                                        <span class="text-neutral-400">{formatBytes(file.size)}</span>
                                        <!-- <span class="text-neutral-400">{(file.timeEst / 60).toFixed(0)}</span> -->
                                    </div>
                                </div>

                                <progress class="progress w-full" value="{file.progress}" max="{file.neededChunks}"></progress>
                            </div>
                        {/each}
                    {/if}
                </div>
            </div>
        {/if}

        <!-- CHOOSE_FILES -->

        <div class="flex gap-8 upload-view h-full {currentView == 'CHOOSE_FILES' ? '' : 'opacity-0'}">
            <div class="flex flex-col max-h-full overflow-y-scroll">
                <input type="file" id="upload" multiple hidden on:change={handleFileChange}/>
                <label for="upload" class="btn btn-outline mb-4 min-w-72 flex-grow w-full">
                    <span>choose files</span>
                </label> 

                <div class="flex flex-col gap-2">
                    {#if files != null}
                        {#each files as file, index}
                            <div class="flex items-center justify-between gap-2">
                                <span>{truncate(file.name, 25)}</span>
                                <div class="flex gap-1">
                                    <span class="text-neutral-400">{formatBytes(file.size)}</span>
                                    <button class="btn btn-circle btn-outline btn-xs" on:click={() => {removeFile(index)}}>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </div>

        
            <div class="flex flex-col gap-2 w-96">
                <label class="form-control w-full">
                    <input type="text" placeholder="Title" class="input input-bordered w-full" bind:value={title}/>
                </label>
        
                <textarea class="textarea textarea-bordered resize-none" placeholder="Comment" bind:value={comment}></textarea>
        
                <label class="input input-bordered flex items-center gap-2">
                    <input type="text" class="grow" placeholder="Password" bind:value={password}/>
                </label>
                
                <label class="input input-bordered flex items-center gap-2">
                    <input type="number" min="1" class="grow" placeholder="Max. downloads" bind:value={maxDownloads}/>
                </label>
        
                <label class="input input-bordered flex items-center gap-2">
                    <input type="number" min="1" class="grow" placeholder="Save duration" bind:value={saveDuration}/>
                </label>
        
                <button class="btn btn-primary" on:click={startUpload}>Upload</button>
            </div>
        </div>
    </div>  
</Modal>

<style>

    .views {
    }

    .upload-view {
        max-height: 70vh;
    }
</style>