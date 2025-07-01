<template>
<span></span>
</template>
<script>
var init = false
var list = []
export default {
    props: ['uri', 'id', 'api', 'action'],
    data() {
        return  {
            init: false
        }
    },
    created() {
        list.push(this)
    },
    mounted() {
        if (init) {
            return
        }
        init = true
        let sc = document.createElement('script')
        sc.src = this.uri + '?onload=ewz&render=' + this.api
        document.body.appendChild(sc)
        window.ewz = () => {
            list.forEach(widget => {
                grecaptcha.ready(()=> {
                    grecaptcha.execute(widget.api, { action: widget.action }).then((token) => {
                      widget.init = true
                      var recaptchaResponse = document.getElementById(widget.id);
                      recaptchaResponse.value = token;
                    });
                  });
            })
        }
    }
}
</script>