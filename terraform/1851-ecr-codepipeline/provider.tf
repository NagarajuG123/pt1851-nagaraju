provider "aws" {
  region = var.region
  default_tags {
    tags = {
      ManagedBy         = "terraform"
      Project           = var.ProjectName
      workspace         = terraform.workspace
      tf_backend_bucket = var.tf_backend_bucket_name
    }
  }
}

terraform {
  backend "s3" {
  }
}
