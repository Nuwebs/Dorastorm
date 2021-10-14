<template>
  <div class="container wp">
    <post-loading v-if="loading"> </post-loading>
    <div v-else>
      <post-creator-options
        :author-id="post.author.id"
        :post-id="post.id"
        class="wp bg-light mt-0"
      ></post-creator-options>
      <div class="wp bg-light">
        <div class="row">
          <div class="col-md-8">
            <img
              :src="post.banner"
              class="img-fluid mb-2 mx-auto d-block rounded post-banner"
            />
            <h1>{{ post.title }}</h1>
            <div class="editor__content" v-html="post.content"></div>
            <hr />
            <div class="row">
              <div class="col-6">
                <span>
                  <b>{{ $t("modules.posts.author") }}</b> {{ post.author.name }}
                </span>
              </div>
              <div class="col-6">
                <span class="d-block">
                  <b>{{ $t("message.created_at") }}</b>
                  {{ post.created | humanDate }}
                </span>
                <span class="d-block text-muted">
                  <b>{{ $t("message.updated_at") }}</b>
                  {{ post.modified | humanDate }}
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <h3>Te recomendamos leer:</h3>
            
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { is404 } from "../../shared/utils/responses";
import PostCreatorOptions from "../../components/posts/PostCreatorOptions";
import PostLoading from "../../components/loading/PostAnimation";
export default {
  components: {
    PostCreatorOptions,
    PostLoading,
  },
  metaInfo() {
    return {
      meta: [
        {
          name: "description",
          content: this.metaDescription,
        },
      ],
      title: this.metaTitle,
    };
  },
  data() {
    return {
      loading: false,
      post: null,
    };
  },
  computed: {
    metaTitle() {
      if (this.post) {
        return this.post.title;
      }
      return this.$t("message.loading");
    },
    metaDescription() {
      if (this.post) {
        return this.post.description;
      }
      return this.$t("message.loading");
    },
  },
  async created() {
    this.loading = true;
    try {
      this.post = (
        await axios.get("/api/posts/" + this.$route.params.slug)
      ).data.data;
      this.loading = false;
    } catch (error) {
      if (is404(error)) {
        this.$toasts.error(this.$t("error.404.specific.post"));
        this.$router.push({
          name: "404",
          params: { 0: "/", locale: this.$route.params.locale },
        });
      } else {
        this.$toasts.error(this.$t("error.fatal"));
      }
    }
  },
};
</script>
<style scoped>
.post-banner {
  max-height: 700px;
}
</style>