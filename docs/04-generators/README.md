# Generators

Deep dive into Traitify's powerful value generator system.

## Overview

Generators are the engine behind Traitify's automatic value generation. They implement a flexible, configurable system for creating UUIDs, tokens, slugs, and custom values. This section covers the three built-in generators and how to configure them for your needs.

## Table of Contents

### [1. Overview](01-overview.md)

Introduction to generators, built-in generators (Token, UUID, Slug), configuration merging, and common use cases.

## Built-in Generators

### TokenGenerator

Generates secure random tokens with configurable length, character pools, and prefixes/suffixes.

**Use Cases**: API keys, session tokens, reset tokens, verification codes

### UuidGenerator

Generates UUIDs in multiple versions (ordered, v1, v3, v4, v5) and formats (string, binary, hex).

**Use Cases**: Primary keys, external IDs, distributed systems, API resources

### SlugGenerator

Creates URL-friendly slugs from text with language support, uniqueness checking, and length limits.

**Use Cases**: Blog post URLs, product permalinks, category paths, user profiles

## Related Documentation

- [Architecture](../02-architecture/README.md) - Generator pattern and design
- [Configuration](../05-configuration/README.md) - Configuration options
- [Advanced](../07-advanced/README.md) - Creating custom generators
