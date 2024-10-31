<template>
  <v-sheet
    class="mt-16 pa-4 text-center mx-auto"
    elevation="5"
    max-width="1200"
    rounded="lg"
    width="100%"
  >
    <v-data-table
      :headers="headers"
      :items="tasks"
      :sort-by="[{ key: 'id', order: 'asc' }]"
    >
      <template v-slot:top>
        <v-toolbar
          flat
        >
          <v-toolbar-title>Task CRUD</v-toolbar-title>
          <v-divider
            class="mx-4"
            inset
            vertical
          ></v-divider>
          <v-spacer></v-spacer>
          <v-dialog
            v-model="dialog"
            max-width="500px"
          >
            <template v-slot:activator="{ props }">
              <v-btn
                class="mb-2"
                color="primary"
                dark
                v-bind="props"
              >
                New Task
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-text-field
                    v-model="editedItem.title"
                    :rules="rules"
                    label="Title"
                  ></v-text-field>
                  <v-textarea
                    label="Description"
                    v-model="editedItem.description"
                    variant="filled"
                    auto-grow
                  ></v-textarea>
                  <v-select
                    v-model="editedItem.status"
                    :items="statuses"
                    :rules="rules"
                    item-title="label"
                    item-value="value"
                    label="Status"
                    persistent-hint
                    return-object
                    single-line
                  ></v-select>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="blue-darken-1"
                  variant="text"
                  @click="close"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="blue-darken-1"
                  variant="text"
                  @click="save"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="closeDelete">Cancel</v-btn>
                <v-btn color="blue-darken-1" variant="text" @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.status="{ value }">
        <v-chip :color="getColor(value)">
          {{ convertStatus(value) }}
        </v-chip>
      </template>
      <template v-slot:item.created_at="{ value }">
        {{ formatDate(value) }}
      </template>
      <template v-slot:item.updated_at="{ value }">
        {{ formatDate(value) }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-btn
          class="me-2"
          color="blue-lighten-2"
          icon="mdi-pencil"
          variant="text"
          size="small"
          @click="editItem(item)"
        ></v-btn>
        <v-btn
          class="me-2"
          color="red"
          icon="mdi-delete"
          variant="text"
          size="small"
          @click="deleteItem(item)"
        ></v-btn>
      </template>
      <template v-slot:no-data>
        There is no data.
      </template>
    </v-data-table>
  </v-sheet>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  data() {
    return {
      tasks: [],
      loading: false,
      rules: [
        v => !!v || 'Field is required',
      ],
      statuses: ["pending", "in-progress", "completed"],
      dialog: false,
      dialogDelete: false,
      headers: [
        {
          title: 'Id',
          align: 'start',
          key: 'id',
        },
        { title: 'Title', key: 'title' },
        { title: 'Description', key: 'description', sortable: false },
        { title: 'Status', key: 'status' },
        { title: 'Created At', key: 'created_at' },
        { title: 'Updated At', key: 'updated_at' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      desserts: [],
      editedIndex: -1,
      editedItem: {
        title: '',
        description: '',
        status: '',
      },
      defaultItem: {
        title: '',
        description: '',
        status: '',
      },
    };
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'New Task' : 'Edit Task'
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
  methods: {
    async fetchTasks() {
      const response = await axios.get('/api/tasks');
      this.tasks = response.data;
    },
    editItem (task) {
      this.editedIndex = this.tasks.indexOf(task)
      this.editedItem = Object.assign({}, task)
      this.dialog = true
    },
    deleteItem (item) {
      this.editedIndex = this.tasks.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },
    async deleteItemConfirm () {
      try {
        await axios.delete(`/api/tasks/${this.tasks[this.editedIndex].id}`);
        this.fetchTasks();
        this.closeDelete()
        this.$toast.open({
          message: 'Task deleted',
        });
      } catch(error) {
        this.$toast.open({
          message: error.response.data.message || 'Something went wrong!',
          type: 'error',
        });
      }
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
    async save () {
      try {
        if (this.editedIndex > -1) {
          await axios.put(`/api/tasks/${this.tasks[this.editedIndex].id}`, this.editedItem);
          this.$toast.open({
            message: 'Task updated',
          });
        } else {
          await axios.post('/api/tasks', this.editedItem);
          this.$toast.open({
            message: 'Task saved',
          });
        }
        this.fetchTasks();
        this.close()
      } catch (error) {
        this.$toast.open({
          message: error.response.data.message || 'Something went wrong!',
          type: 'error',
        });
      }
    },
    getColor (value) {
      if (value == 'pending') return 'red'
      else if (value == 'in-progress') return 'orange'
      else return 'green'
    },
    formatDate(dateString) {
      return moment(dateString).format('YYYY-MM-DD HH:mm:ss')
    },
    convertStatus (value) {
      if (value == 'pending') return 'Pending'
      else if (value == 'in-progress') return 'In Progress'
      else return 'Completed'
    },
  },
  mounted() {
    this.fetchTasks();
  },
};
</script>
