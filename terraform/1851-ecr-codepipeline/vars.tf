variable "environment" {
  default = "dev-all"
}
variable "source_branch_name" {
  default = "development"
}
variable "tf_backend_bucket_name" {
  default = "tf-state-keeper-nagaraju"
}
variable "ProjectName" {
  default = "api2"
}
variable "region" {
  default = "us-east-1"
}
variable "repo_id" {
  default = "GARAGANAGARAJU/perals-1851-project"
}
variable "codebuild_bucket" {
  default = "codebuild-nagaraju"
}
variable "codepipeline_bucket" {
  default = "codepipeline-nagaraju"
}