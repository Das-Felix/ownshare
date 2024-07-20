<script>
	import { onMount } from "svelte";
    import FileUploadModal from "../../../lib/components/FileUploadModal.svelte";
    import { fetchFileCollections, fetchFileCollection, deleteFileCollections } from "$lib/api.js";
    import { base } from '$app/paths'

    $: fileCollections = [];
    
    let showUploadModal = false;
    let loading = true;
    let pageError = null;

    let tableCategorys = [
        {title: "Name", field: "title"}, 
        {title: "Files", field: "totalFiles"},
        {title: "Total Size", field: "totalSize"},
        {title: "Downloads", field: "downloads"},
        {title: "Uploaded by", field: "uploaded_by"},
        {title: "Uploaded at", field: "uploaded_at"},
        {title: "Expiry Date", field: "save_until"}
    ]

    let tableSort = {
        category: "uploaded_at",
        dir: "DESC"
    }

    let tablePage = 1;
    let tablePagesTotal = 1;
    let filesPerPage = 10;

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatDate(date) {
        const [datePart] = date.split(' '), [year, month, day] = datePart.split('-');
        return `${day}.${month}.${year}`;
    }

    async function changeSort(category) {
        if(tableSort.category == category.field) {
            tableSort.dir = tableSort.dir == "ASC" ? "DESC" : "ASC";
        } else {
            tableSort.category = category.field;
            tableSort.dir = "DESC";
        }


       await fetchTableData();
    }

    onMount(async () => {
        await fetchTableData();
    });

    async function fetchTableData() {
        loading = true;
        let collections = await fetchFileCollections(tableSort.category, tableSort.dir, tablePage, filesPerPage);
        fileCollections = collections.collections;
        tablePagesTotal = Math.ceil(collections.total / filesPerPage);

        if(fileCollections.error != null) {
            pageError = fileCollections.error;
        }

        loading = false;
    }

</script>

<!-- Upload File Modal -->

<FileUploadModal bind:showUploadModal on:uploadFinished={async () => {await fetchTableData();}}></FileUploadModal>

<!-- File List -->

<div class="flex justify-between items-center mb-4"> 
    <h1 class="text-4xl font-bold">File Collections</h1>

    <div>
        <button class="btn btn-accent btn-sm" on:click={() => {showUploadModal = true}}>Upload</button>
    </div>
</div>

<div>
    {#if pageError != null}
        <p>Error: {pageError}</p>
    {/if}

    {#if pageError == null}
        <div class="overflow-x-auto w-full">
            <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    {#each tableCategorys as category}
                        <th class="whitespace-nowrap" on:click={() => {changeSort(category)}}>{category.title} 
                            {#if tableSort.category == category.field}
                                <img src="/icon/arrow_down.svg" class="{tableSort.dir}" alt="arrow">
                            {:else}
                                <img src="/icon/arrow_down.svg" class="opacity-0" alt="arrow">
                            {/if}
                        </th>
                {/each} 

                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                {#each fileCollections as collection, i}
                    <tr class="bg-base-200">
                        <td class="whitespace-nowrap">{collection.title}
                            {#if collection.password != "" }
                                <img class="h-4 inline ml-2" src="/icon/lock.svg" alt="">
                            {/if}
                        </td>
                        <td>{collection.totalFiles}</td>
                        <td class="whitespace-nowrap">{formatBytes(collection.totalSize)}</td>
                        <td>{collection.downloads}/{collection.max_downloads}</td>
                        <td>
                            {#if collection.uploaded_by != null}
                                {collection.uploaded_by.username}
                            {/if}
                        </td>
                        <td>{formatDate(collection.uploaded_at)}</td>
                        <td>{formatDate(collection.save_until)}</td>
                        <td class="flex flex-nowrap gap-1">
                            <a class="btn btn-secondary btn-sm p-0 aspect-square" href="{base}/?q={collection.collection_id}">
                                <img src="{base}/icon/link.svg" alt="">   
                            </a>
                            <!-- <button class="btn btn-secondary btn-sm p-0 aspect-square">
                                <img src="/icons/edit.svg" alt="">   
                            </button> -->
                            <button class="btn btn-error btn-sm p-0 aspect-square" on:click={async () => {
                                    await deleteFileCollections(collection.collection_id);
                                    await fetchTableData();
                                }}>
                                <img src="{base}/icon/delete.svg" alt="">   
                            </button>
                        </td>
                    </tr>
                    <br>
                {/each}
            </tbody>
            </table>

            {#if loading}
                <div class="w-full flex justify-center items-center h-full">
                    <span class="loading loading-dots loading-lg"></span>
                </div>
            {/if}   
        </div>

        <div class="flex gap-2 justify-center">
            

            {#each new Array(tablePagesTotal) as _, i}
                <button 
                    on:click={async () => {
                        tablePage = i + 1;
                        await fetchTableData();
                    }} class="btn btn-sm {tablePage == (i + 1) ? 'btn-accent' : ''}">
                    {i + 1}
                </button>
            {/each}
        </div>
    {/if} 
</div>

<style>
    tr th:hover {
        @apply bg-base-200;
    } 

    tr th {
        @apply rounded-t-md;
    }

    tr th img {
        @apply inline h-4 float-end;
    }

    tr th img.DESC {
        transform: rotate(180deg);
    }
</style>