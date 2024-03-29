<template>
  <article class="container wp bg-light">
    <div v-if="loading">{{ $t("message.loading") }}</div>
    <div v-else>
      <div v-if="!success">{{ $t("error.fatal") }}</div>
      <div v-else>
        <h3>{{ $t("modules.users.user_info") }}</h3>
        <form @submit.prevent="submit">
          <section class="form-group">
            <validation-error :errors="errors" name="name" #default="{ e }">
              <label for="name">{{ $t("modules.users.name") }}</label>
              <input
                type="text"
                name="name"
                class="form-control"
                v-model="updatedUser.name"
                :class="[{ 'is-invalid': e }]"
                required
              />
            </validation-error>
          </section>
          <section class="form-group">
            <validation-error :errors="errors" name="email" #default="{ e }">
              <label for="email">{{ $t("message.email") }}</label>
              <input
                type="email"
                name="email"
                class="form-control"
                v-model="updatedUser.email"
                :class="[{ 'is-invalid': e }]"
                required
              />
            </validation-error>
          </section>
          <div v-if="loadingAvailableRoles" key="loadingRoles">
            {{ $t("message.loading") }}
          </div>
          <section v-else key="loadingRoles">
            <div
              class="form-group"
              v-if="
                checkUserPermission(loggedUser, corePms.UPDATE_USERS) &&
                availableRoles.length > 0
              "
            >
              <validation-error
                :errors="errors"
                name="role_id"
                #default="{ e }"
              >
                <label for="role_id">{{
                  $t("modules.users.role_select")
                }}</label>
                <select
                  name="role_id"
                  class="custom-select form-control"
                  v-model="updatedUser.role.id"
                  :class="[{ 'is-invalid': e }]"
                  required
                >
                  <option disabled value="">
                    {{ $t("modules.users.role_default") }}
                  </option>
                  <option
                    :value="role.id"
                    v-for="role in availableRoles"
                    :key="role.id"
                  >
                    {{ role.name }}
                  </option>
                </select>
              </validation-error>
            </div>
          </section>
          <input
            type="submit"
            :value="$t('modules.users.update')"
            class="btn btn-primary btn-block"
            :disabled="submiting || loadingAvailableRoles"
          />
        </form>
        <hr />
        <button
          class="btn btn-warning"
          @click.prevent="changePassword"
          :class="[{ 'd-none': changingPassword }]"
        >
          {{ $t("modules.users.change_password") }}
        </button>
        <div v-if="changingPassword">
          <user-password-update
            :user-id="userId"
            @cancel="changePassword"
          ></user-password-update>
        </div>
      </div>
    </div>
  </article>
</template>
<script>
import * as Responses from "../../shared/utils/responses";
import Obj from "../../shared/utils/object-utils";
import FormTraits from "../../shared/mixins/form-traits";
import ValidationError from "../../components/ValidationError";
import PermissionsHandling from "../../shared/mixins/permissions-handling";
import Auth from "../../services/auth";
import UserPasswordUpdate from "../../components/users/UserPasswordUpdate";
export default {
  mixins: [FormTraits, PermissionsHandling],
  components: {
    ValidationError,
    UserPasswordUpdate,
  },
  props: {
    userId: {
      type: [Number, String],
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      loadingAvailableRoles: false,
      errors: null,
      success: true,
      submiting: false,
      changingPassword: false,
      availableRoles: null,
      updatedUser: null,
      updatingOtherUser: false,
    };
  },
  created() {
    // If the logged user is trying to modify other user data
    // we need to check if he have the permission to do it
    if (this.loggedUser.id != this.userId) {
      this.updatingOtherUser = true;
    }
    if (
      this.updatingOtherUser &&
      !this.checkUserPermission(this.loggedUser, this.corePms.UPDATE_USERS)
    ) {
      this.$router.push({
        name: "403",
        params: { locale: this.$route.params.locale },
      });
    }
  },
  async beforeMount() {
    this.loading = true;
    if (this.updatingOtherUser) {
      try {
        this.updatedUser = (
          await axios.get("/api/users/" + this.userId)
        ).data.data;
      } catch (error) {
        this.success = false;
        if (Responses.is404(error)) {
          this.$toasts.error($t("error.404.specific.user"));
        }
      }
    } else {
      this.updatedUser = Obj.clone(this.loggedUser);
    }
    this.loading = false;
    // Improve
    this.loadingAvailableRoles = true;
    try {
      this.availableRoles = (await Auth.userRolesBelow()).data.data;
      this.loadingAvailableRoles = false;
    } catch (error) {
      this.$toasts.error($t("error.fatal"));
    }
  },
  methods: {
    changePassword() {
      this.changingPassword = !this.changingPassword;
    },
    async submit() {
      this.submiting = true;
      this.updatedUser.role_id = this.updatedUser.role.id;
      try {
        await axios.patch("/api/users/" + this.userId, this.updatedUser);
        this.$toasts.success(this.$t("message.data_changed"));
        if (this.userId === this.$store.getters.getUserID) {
          let user = await Auth.loadUser();
          this.$store.commit("setUser", user);
        }
      } catch (error) {
        if (Responses.is404(error)) {
          this.$toasts.error(this.$t("error.404.specific.user"));
        }
        if (Responses.is409(error)) {
          this.$toasts.error(this.$t("error.409.specific.last_admin"));
        }
        if (Responses.is422(error)) {
          this.errors = error.response.data.errors;
        }
      }
      this.submiting = false;
    },
  },
};
</script>