<script>
    import Modal from "./Modal.svelte";

    import { createEventDispatcher } from 'svelte';
    import { getConfig } from "$lib/api.js";
    import { addMessage } from "$lib/stores.js";

	const dispatch = createEventDispatcher();

    let error = "";

    let files;


    async function handleUpload() {

        if(files == null) {
            error = "No file selected!";
            return;
        }
    
        if(files[0].type != "application/zip") {
            error = "Theme has to be a zip folder!";
            return;
        }

        const formData = new FormData();
        formData.append('fileList[]', files[0]);

        const cfg = await getConfig();

        try {
            const response = await fetch(`${cfg.backendAddress}/admin/theme/uploadTheme.php`, {
                method: 'POST',
                credentials: "include",
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                if (result.error) {
                    error = result.error;
                } else {
                    error = "";
                    addMessage(result.message, "success");
                    dispatch("uploaded", { message: result.message });
                    showModal = false;
                }
            } else {
                error = "Failed to upload the file.";
            }
        } catch (err) {
            error = "An error occurred while uploading the file.";
        }
       

        dispatch("uploaded", {});


    }

    export let showModal = true;
</script>

<Modal bind:showModal={showModal}>
    <h2 class="text-2xl font-bold">Upload Theme</h2>

    <span class="text-red-400">{error}</span>

    <div class="flex flex-col gap-3 w-72 mt-4">
        <input type="file" class="file-input file-input-bordered w-full max-w-xs file-input-primary" bind:files value="" />

        <button class="btn btn-primary" on:click={handleUpload}>Upload Theme</button>
    </div>
</Modal>