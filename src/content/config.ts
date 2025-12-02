import { defineCollection, z } from 'astro:content'

const blog = defineCollection({
    type: 'content',
    schema: z.object({
        title: z.string(),
        description: z.string(),
        pubDate: z.coerce.date(),
        updatedDate: z.coerce.date().optional(),
        heroImage: z.string().optional(),
        tags: z.array(z.string()).optional(),
    }),
});

const art = defineCollection({
    type: 'content',
    schema: z.object({
        name: z.string(),
        subtitle: z.string().optional(),
        medium: z.string(),
        dimensions: z.string(),
        year: z.number(),
        heroImage: z.string(),
        galleryImages: z.array(z.string()).optional(), // Additional images for gallery view
        status: z.string().optional(), // Sold/Available
    }),
});


export const collections = { blog, art }

