<template>
    <div class="alert">
    <modal v-if="!hidden" >
        <p class="text" slot="body">
            <slot name="body">
                {{ text }}
            </slot>
        </p>
        <div class="foot" slot="footer">
            <slot name="footer">
              <button class="button small-button" type="button" @click="$emit('close'); hidden = true">
                    OK
              </button>
            </slot>
        </div>
    </modal>
    </div>
</template>
<script> 
    import Modal from './Modal';
    export default {
        name :'alert',
        components: {Modal},
        props : {
            text : {
                type: String
            }
        },
        data : function() {
            return {
                hidden : false
            }
        }
    }
</script>
<style>
.alert div.foot {
    text-align: center;
}
.alert p.text {
    text-align: center;
}
</style>