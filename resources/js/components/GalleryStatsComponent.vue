<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card" v-if="showUserStat">
                    <div class="card-header">Users Who Favorited the Most</div>
                    <div class="card-body">
                            <div class="table-responsive-sm">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr>
                                      <th>Users Name</th>
                                      <th>Favorited</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="item in Stats">
                                      <td> {{ item.name }}</td>
                                      <td> {{ item.favorited }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                            </div>
                    </div>
                </div>

                <div class="card" v-if="showPhotoStat">
                    <div class="card-header">Most Favorited Five Photos</div>
                    <div class="card-body">
                            <div class="table-responsive-sm">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr>
                                      <th>Photo</th>
                                      <th>Favorited</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="item in Stats">
                                      <td><img :src="item.thumbnailUrl" class="img-thumbnail" >
                                      <span class="ml-1 font-weight-bold">#{{item.photo_id}}</span>
                                      </td>
                                      <td> {{ item.favorite }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                            </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</template>

<script>
    export default {

        data: function () {
          return {
            Stats: null,
            showUserStat: false,
            showPhotoStat: false,

          }
        },
        mounted() {
            console.log('Component mounted.')
            axios.get('api/stats')
              .then((response) => {
                if( response.data.Photos_Stats !== undefined){
                    this.showPhotoStat = true;
                    this.Stats = response.data.Photos_Stats;
                    console.log( response.data.Photos_Stats );
                }

                if( response.data.Users_Stats !== undefined){
                    this.showUserStat = true;
                    this.Stats = response.data.Users_Stats;
                    console.log( response.data.Users_Stats );
                }                
                  
              })
              .catch((error) => {
                console.log(error);
              });




        }


    }
</script>
<!-- Styles -->
<style>
    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }
</style>
