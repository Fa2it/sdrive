<template>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6 mb-3" v-for="(item, key, index) in gallery">
                <img :src="item.thumbnailUrl" class="rounded" >
                 <div class="d-block">{{ item.title | subStr }}</div>
                 <button id="2" v-on:click="setFavorite(item, key )" 
                        class="btn btn-sm" v-bind:class="setFavoriteCls(item)">{{item.favorite}}</button>
                 <span class="ml-2 font-weight-bold">#{{item.id}}</span>
            </div>                    
        </div>

        <div class=" alert justify-content-center card" v-if="isPagination">
          <ul class="pagination  justify-content-center">
            <li class="page-item"><a v-on:click="previousPage()" class="page-link" href="#">Previous</a></li>
            <li class="page-item" v-for="(page, key, index) in pages"><a v-on:click="pagination(page)"  class="page-link" href="#">{{page}}</a></li>            
            <li class="page-item"><a v-on:click="nextPage()"     class="page-link" href="#">Next</a></li>
          </ul> 
        </div>

    </div>
</template>

<script>
    export default {

        data: function () {
          return {
            gallery: null,
            start: 1,
            limits: 20,
            AuthStr:'Bearer '+ this.usertoken,
            isPagination: false,
            pages:[1,2,3,4,5,6], 

          }
        },
        props: {
            usertoken: String,
            uuid: String,
        },
        filters: {
            subStr: function(string) {
                return string.substring(0,15) + '...';
                }
          
        },
        methods: {
            setFavorite( item , key ) {
                // Ajaxpost but must toggle Like to Unlike
                if( item.favorite == "Like" ){
                    this.like(item, key );
                } else {
                    this.unlike(item, key);
                }   
            },
			setFavoriteCls( item ){
				return { 'btn-secondary': item.btnSecondary, 'btn-primary': item.btnPrimary };
			},
            like(item, key){
                this.gallery[key].favorite = "UnLike";
				this.gallery[key].btnPrimary = true;
				this.gallery[key].btnSecondary = false;
				
                let url = 'api/photo/create';
                let postData = {
                        user_id : this.uuid, 
                        photo_id : item.id, 
                        title : item.title, 
                        thumbnailUrl : item.thumbnailUrl,
                    };
                let axiosConfig = {
                        headers: { Authorization: this.AuthStr }

                };
       
                axios.post(url, postData, axiosConfig)
                .then((response) => {                      
                  
                })
                .catch((error) => {
                console.log(error);
                });

            },
            unlike(item, key){
                this.gallery[key].favorite = "Like"; 
				this.gallery[key].btnPrimary = false;
				this.gallery[key].btnSecondary = true;
                let url = 'api/photo/delete/' + item.id;
                axios.delete(url, { headers: { Authorization: this.AuthStr } })
                .then((response) => {              
                  
                })
                .catch((error) => {
                console.log(error);
                }); 


            },
            getGallery(){
                let url = 'api/photos/' + this.start +'/'+this.limits;
                axios.get(url, { headers: { Authorization: this.AuthStr } })
                .then((response) => {
                if( response.data.Photos !== undefined){
				console.log( response.data.Photos );
                    this.gallery = response.data.Photos;
                    this.isPagination = true;
                }               
                  
                })
                .catch((error) => {
                console.log(error);
                });            
            },
            pagination(num, event){
                if (event) event.preventDefault()
                this.start = num;
                this.getGallery();
            },
            previousPage(){
                if( this.start > 1 ){
                    this.start = this.start - 1;
                    this.getGallery();
                }
                   
            },
            nextPage(){
                if( this.start < 6 ){
                    this.start = this.start + 1; 
                    this.getGallery();               
                }

            }
        },
        mounted() {
            this.getGallery();
            
        }
    }
</script>
