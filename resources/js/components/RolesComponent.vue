<template>
  <v-data-table
    :headers="headers"
    :items="roles.data"
    @pagination="paginate"
    :items-per-page=5
    :server-items-length="roles.total"
    sort-by="calories"
    class="elevation-1"
    show-select
    @input='selectAll'
    :loading="loading"
    loading-text="Loading... Please wait"
    :footer-props="{
    itemsPerPageOptions:[5,10,15],
    showCurrentPage:true,
    showFirstLastPage:true,
    itemsPerPageText:'Role per page'}"
  >
    <template v-slot:top>
      <v-toolbar
        flat
      >
        <v-toolbar-title>Role Manager</v-toolbar-title>
        <v-divider
          class="mx-4"
          inset
          vertical
        ></v-divider>
        <v-text-field label="Search Role" @input="searchIt" single-line append-icon="mdi-magnify"/>
        <v-spacer></v-spacer>
        <v-dialog
          v-model="dialog"
          max-width="500px"
        >
          <template v-slot:activator="{ on }">
            <v-btn
              color="primary"
              dark
              class="mb-2"
              v-bind="attrs"
              v-on="on"
            >
              New Item
            </v-btn>
            <v-btn
              color="success"
              dark
              class="mb-2"
              @click='deleteAll'
            >
              Delete All
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="headline">{{ formTitle }}</span>
    		</v-card-title>
            <v-card-text>
              <v-container>

                <v-row>
                  <v-col
                    cols="12"
                    sm="12"
                  >
                    <v-text-field
                      v-model="editedItem.name"
                      label="Role name"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
			 <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="blue darken-1"
                text
                @click="close"
              >
                Cancel
              </v-btn>
              <v-btn
                color="blue darken-1"
                text
                @click="save"
              >
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="headline">Are you sure you want to delete this item?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">Cancel</v-btn>
              <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
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
    </template>
    <template v-slot:item.actions="{ item }">
      <v-icon
        small
        class="mr-2"
        @click="editItem(item)"
      >
        mdi-pencil
      </v-icon>
      <v-icon
        small
        @click="deleteItem(item)"
      >
        mdi-delete
      </v-icon>
    </template>
    <template v-slot:no-data>
      <v-btn
        color="primary"
        @click="initialize"
      >
        Reset
      </v-btn>
    </template>
  </v-data-table>
</template>
<script>
  export default {
    data: () => ({
      dialog: false,
      selected: [],
      snackbar:false,
      text: "Role Updated Successfully",
      dialogDelete: false,
      headers: [
        {
          text: '#',
          align: 'start',
          sortable: false,
          value: 'id',
        },
        { text: 'Name', value: 'name' },
        { text: 'Created At', value: 'created_at' },
        { text: 'Updated At', value: 'updated_at' },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      roles: [],
      loading:false,
      editedIndex: -1,
      editedItem: {
      	id:'',
        name: '',
        created_at: '',
        updated_at: ''
      },
      defaultItem: {
      	id:'',
        name: '',
        created_at: '',
        updated_at: ''
      },
    }),

    computed: {
      formTitle () {
        return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
      },
    },

    watch: {
      dialog (val) {
        val || this.close()
      },
      dialogDelete (val) {
        val || this.closeDelete()
      },
    },

    created () {
      this.initialize()
    },

    methods: {
      selectAll(e){
      this.selected = [];
      if(e.length>0){
      this.selected = e.map(val=>val.id)
      }
      },
      deleteAll(){
      let decide = confirm('Are you sure? you want to delete these items?')
      if(decide){
      axios.post('api/roles/delete',{'roles':this.selected})
      .then(res => {
      this.text = "Record Deleted Successfully";
      this.snackbar = true
      this.selected.map(val => {
      const index = this.roles.data.indexOf(val)
      this.roles.data.splice(index,1)
      })
      })
      }
      },
      searchIt(e){
      if(e.length>3){
      	axios.get(`api/roles/${e}`)
      	.then(res => this.roles = res.data.roles)
      	.catch(err => console.dir(err.response))
      	}
      if(e.length<=0){
      	axios.get('/api/roles')
      	.then(res => this.roles = res.data.roles)
      	.catch(err => console.dir(err.response))
		}
      },
      paginate(e){
      axios.get(`/api/roles?page=${e.page}`, {params:{'per_page': e.itemsPerPage}}).then(res => this.roles = res.data.roles)
	  .catch(err => {
	  if(err.response.status == 401)
	  localStorage.removeItem('token');
	  this.$router.push('/login');
	  })
      },
      initialize () {
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
      },

      editItem (item) {
        this.editedIndex = this.roles.data.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialog = true
      },

      deleteItem (item) {
        this.editedIndex = this.roles.data.indexOf(item)
        this.editedItem = Object.assign({}, item)
        this.dialogDelete = true
      },

      deleteItemConfirm () {
      	axios.delete('api/roles/'+this.editedItem.id)
      	.then(res =>  {this.roles.data.splice(this.editedIndex, 1), this.closeDelete(), this.snackbar=true, this.text="Role Deleted Successfully"})
      	.catch(err => this.snackbar=true, this.text="Role Not delete")
        
        
      },

      close () {
        this.dialog = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      closeDelete () {
        this.dialogDelete = false
        this.$nextTick(() => {
          this.editedItem = Object.assign({}, this.defaultItem)
          this.editedIndex = -1
        })
      },

      save () {
        if (this.editedIndex > -1) {
        axios.put('api/roles/'+this.editedItem.id,{'name':this.editedItem.name})
        .then(res => {this.snackbar = true, Object.assign(this.roles[this.editedIndex], this.editedItem),this.text="Role updated", this.close()})
        .catch(err => {this.snackbar = true, this.text="Role not updated"})
        
        .catch(err => alert('Update Fails'))
        } else {
          axios.post('api/roles',{'name':this.editedItem.name})
          .then(res => {this.roles.push(res.data[1]),this.close()})
          .catch(err => console.dir(err.response))
        }
      },
    },
  }
</script>

<style>
</style>
