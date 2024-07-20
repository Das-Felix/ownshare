<script>
    import { goto } from '$app/navigation';
	import { onMount } from "svelte";
    import { deleteUser, fetchUsers } from "$lib/api.js";
    import CreateUserModal from "../../../lib/components/CreateUserModal.svelte";
    import { base } from '$app/paths'

    $: users = [];

    let pageError = "";
    let showCreateUserModal = false;
    let loading = true;


    onMount(async () => {
        users = await fetchUsers();
        loading = false;
    });

</script>

<CreateUserModal bind:showModal={showCreateUserModal} on:created={async () => { users = await fetchUsers() }}></CreateUserModal>

<div class="flex justify-between items-center mb-4"> 
    <h1 class="text-4xl font-bold">Users</h1>

    <div>
        <button class="btn btn-accent btn-sm" on:click={() => {showCreateUserModal = true}}>Create User</button>
    </div>
</div>

<span class="mb-4 text-red-500">{pageError}</span>

<div class="overflow-x-auto w-full">
    <table class="table">
        <thead>
            <tr>
            <th>Id</th>
            <th>Username</th>
            <th>E-Mail</th>
            <th>Role</th>
            <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            {#if users != null && users.length > 0}
                {#each users as user, i}
                    <tr class="bg-base-200">
                        <td>{user.id}</td>
                        <td>{user.username}</td>
                        <td>{user.email}</td>
                        <td>{user.role}</td>
                        <td>
                            <!-- <button class="btn btn-secondary btn-sm p-0 aspect-square">
                                <img src="/icons/edit.svg" alt="">   
                            </button> -->
                            <button class="btn btn-error btn-sm p-0 aspect-square" on:click={async () => {
                                    await deleteUser(user.id);
                                    users = await fetchUsers();
                                }}>
                                <img src="{base}/icon/delete.svg" alt="">   
                            </button>
                        </td>
                    </tr>
                    <br>
                {/each}
            {/if}
        </tbody>
    </table>

    {#if loading}
        <div class="w-full flex justify-center items-center h-full">
            <span class="loading loading-dots loading-lg"></span>
        </div>
    {/if}   
</div>
