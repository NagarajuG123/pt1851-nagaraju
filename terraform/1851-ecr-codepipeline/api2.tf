module "EcrCodepipeline-app" {
  source                  = "github.com/pt1851/terraform-ecs-codepipeline?ref=main"
  publication             = var.publication
  environment             = var.environment
  image_name              = "app"
  build_spec_path         = "buildspec/buildspec-app.yml"
  vpc_id                  = data.aws_vpc.vpc.id
  subnet_id               = [data.aws_subnets.private_subnets_id.ids]
  source_branch_name      = var.source_branch_name
  region                  = var.region
  codebuild_bucket        = var.codebuild_bucket
  codepipeline_bucket     = var.codepipeline_bucket
  repo_id                 = var.repo_id
  common_name             = local.common_name
  codebuild_build_timeout = "15"


  stages = [
    {
      name = "Source"
      action = [
        {
          "name"             = "Source"
          "category"         = "Source"
          "owner"            = "AWS"
          "provider"         = "CodeStarSourceConnection"
          "version"          = "1"
          "input_artifacts"  = []
          "output_artifacts" = ["source_output"]

          "configuration" = {
            ConnectionArn    = module.EcrCodepipeline-app.codestar_arn
            FullRepositoryId = var.repo_id
            BranchName       = var.source_branch_name
            DetectChanges    = false
          }
        }
      ]
    },
    {
      name = "Build"
      action = [
        {
          "name"             = "Build"
          "category"         = "Build"
          "owner"            = "AWS"
          "version"          = "1"
          "provider"         = "CodeBuild"
          "input_artifacts"  = ["source_output"]
          "output_artifacts" = ["build_output"]
          "configuration" = {
            ProjectName = module.EcrCodepipeline-app.codebuild_name
          }
        }
      ]
    }
  ]
}


module "EcrCodepipeline-nginx" {
  source                  = "github.com/PearlThoughts2/terraform-ecs-codepipeline?ref=main"
  publication             = var.publication
  environment             = var.environment
  image_name              = "nginx"
  build_spec_path         = "buildspec/buildspec-nginx.yml"
  vpc_id                  = data.aws_vpc.vpc.id
  subnet_id               = [data.aws_subnets.private_subnets_id.ids]
  source_branch_name      = var.source_branch_name
  region                  = var.region
  repo_id                 = var.repo_id
  common_name             = local.common_name
  codebuild_build_timeout = "15"

  stages = [
    {
      name = "Source"
      action = [
        {
          "name"             = "Source"
          "category"         = "Source"
          "owner"            = "AWS"
          "provider"         = "CodeStarSourceConnection"
          "version"          = "1"
          "input_artifacts"  = []
          "output_artifacts" = ["source_output"]
          "configuration" = {
            ConnectionArn    = module.EcrCodepipeline-nginx.codestar_arn
            FullRepositoryId = var.repo_id
            BranchName       = var.source_branch_name
            DetectChanges    = false
          }
        }
      ]
    },
    {
      name = "Build"
      action = [
        {
          "name"             = "Build"
          "category"         = "Build"
          "owner"            = "AWS"
          "version"          = "1"
          "provider"         = "CodeBuild"
          "input_artifacts"  = ["source_output"]
          "output_artifacts" = ["build_output"]
          "configuration" = {
            ProjectName = module.EcrCodepipeline-nginx.codebuild_name
          }
        }
      ]
    }
  ]
}
