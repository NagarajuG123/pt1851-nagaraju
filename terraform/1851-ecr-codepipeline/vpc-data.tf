data "aws_vpc" "vpc" {

  filter {
    name   = "tag:Environment"
    values = ["${var.environment}"]
  }

  filter {
    name   = "tag:Project"
    values = ["1851"]
  }

  filter {
    name   = "tag:ManagedBy"
    values = ["terraform"]
  }

}

data "aws_subnets" "private_subnets_id" {

  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.vpc.id]
  }

  filter {
    name   = "tag:Type"
    values = ["private"]
  }

  filter {
    name   = "tag:ManagedBy"
    values = ["terraform"]
  }

}
data "aws_subnets" "public_subnets_id" {

  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.vpc.id]
  }

  filter {
    name   = "tag:Type"
    values = ["public"]
  }

  filter {
    name   = "tag:ManagedBy"
    values = ["terraform"]
  }

}