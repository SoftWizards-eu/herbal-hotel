<template>
    <video-embed css="embed-responsive-16by9" :src="url"></video-embed>
</template>
<script> 
    import Modal from './Modal';
    export default {
        props : {
            url : {
                type: String
            }
        }
    }
</script>
