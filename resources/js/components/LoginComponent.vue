<template>
<v-app id="inspire">
    <v-content>
      <v-container fluid fill-height>
        <v-layout align-center justify-center>
          <v-flex xs12 sm8 md4>
            <v-card class="elevation-12">
              <v-toolbar dark color="primary">
                <v-toolbar-title>Login Form</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-tooltip right>
                  <template v-slot:activator="{ on }">
                    <v-btn icon large href="https://codepen.io/johnjleider/pen/wyYVVj" target="_blank" v-on="on">
                      <v-icon large>mdi-codepen</v-icon>
                    </v-btn>
                  </template>
                  <span>Codepen</span>
                </v-tooltip>
              </v-toolbar>
               
              <v-card-text>
              <v-progress-linear
			        :active="loading"
			        :indeterminate="loading"
			        absolute
			        top
			        color="deep-purple accent-4"
			      ></v-progress-linear>
                <v-form
                v-model="valid"
                >
                  <v-text-field prepend-icon="person" name="login" label="Login" type="email" v-model="email" :rules="emailRules" required></v-text-field>

                  <v-text-field id="password" prepend-icon="lock" name="password" label="Password" type="password" v-model="password" :rules="passwordRules"></v-text-field>
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" :disabled="!valid" v-on:click='login'>Login</v-btn>
              </v-card-actions>
            </v-card>
          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
    <v-snackbar
    v-model="snackbar"
    >
      {{ text }}

      <template v-slot:action="{ attrs }">
        <v-btn
          color="pink"
          text
          v-bind="attrs"
          @click="snackbar = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>
<script>
export default{
	data(){
	return{
	email:'',
	password:'',
	loading: false,
	snackbar: false,
	text:'',
	emailRules: [
        v => !!v || 'E-mail is required',
        v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
    passwordRules: [
    	v => !!v || 'E-mail is required'
    ]
	}
	},
	created () {
    this.$vuetify.theme.dark = true
    this.snackbar = false
  },
	methods:{
	login: function(){
		axios.interceptors.request.use((config)=>{
	    this.loading = true;
	    return config;
	  }, (error) => {
	   this.loading = false;
	    return Promise.reject(error);
	  });

	// Add a response interceptor
	axios.interceptors.response.use((response)=>{
	    this.loading = false
	    return response;
	  }, (error) => {
	    this.loading = false;
	    return Promise.reject(error);
	  });
	  axios.post('/api/login',{'email':this.email,'password':this.password})
	  .then(res => {
	  	
		 localStorage.setItem('token',res.data.token)
		 localStorage.setItem('loggedIn', true)
		 this.$router.push('/admin').then(res=>console.log('LoggedIn Successfully')).catch(err)
	  })
	  .catch(err=>{
	  this.text=err.response.status
	  this.snackbar = true;
	  })
	}
	}
}
</script>
<style>

</style>