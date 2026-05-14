<template>
    <div class=" min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="max-w-3xl bg-gradient-to-b from-primary to-30% to-transparent rounded-[56px] p-[0.3rem]">
            <div class="h-full w-full m-0 py-14 px-12 bg-white rounded-[53px]">
                <slot />
            </div>
        </div>
    </div>
</template>
