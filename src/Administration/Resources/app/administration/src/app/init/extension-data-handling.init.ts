/* eslint-disable @typescript-eslint/prefer-promise-reject-errors */
/**
 * @sw-package framework
 */

import type Repository from '../../core/data/repository.data';

function getRepository(
    entityName: keyof EntitySchema.Entities,
    additionalInformation: { _event_: MessageEvent<string> },
): Repository<keyof EntitySchema.Entities> | null {
    const extensionName = Object.keys(Shopwell.Store.get('extensions').extensionsState).find((key) =>
        Shopwell.Store.get('extensions').extensionsState[key].baseUrl.startsWith(additionalInformation._event_.origin),
    );

    if (!extensionName) {
        throw new Error(`Could not find a extension with the given event origin "${additionalInformation._event_.origin}"`);
    }

    const extension = Shopwell.Store.get('extensions').extensionsState?.[extensionName];
    if (!extension) {
        throw new Error(
            // eslint-disable-next-line max-len
            `Could not find an extension with the given name "${extensionName}" in the extension store (Shopwell.Store.get('extensions').extensionsState)`,
        );
    }

    if (extension.integrationId) {
        return Shopwell.Service('repositoryFactory').create(entityName, '', {
            'sw-app-integration-id': extension.integrationId,
        });
    }

    return Shopwell.Service('repositoryFactory').create(entityName);
}

function rejectRepositoryCreation(entityName: string): unknown {
    return Promise.reject(`Could not create repository for entity "${entityName}"`);
}

/**
 * This method mutates the result object and removes the filter properties
 * @param result
 * @param customContext
 */
// eslint-disable-next-line max-len
/* eslint-disable @typescript-eslint/no-explicit-any, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument */
function filterContext(result: any, customContext: any) {
    if (result === null || result === 'undefined') {
        return;
    }

    if (typeof result === 'object') {
        // eslint-disable-next-line no-restricted-syntax
        for (const key in result) {
            if (key === 'context') {
                // delete everything inside context except properties of customContext
                // eslint-disable-next-line no-restricted-syntax
                for (const contextKey in result[key]) {
                    if (!customContext || !customContext[contextKey]) {
                        delete result[key][contextKey];
                    }
                }
            } else {
                filterContext(result[key], customContext);
            }
        }
    }
}

/**
 * @private
 */
export default function initializeExtensionDataLoader(): void {
    Shopwell.ExtensionAPI.handle(
        'repositorySearch',
        async ({ entityName, criteria = new Shopwell.Data.Criteria(), context }, additionalInformation) => {
            try {
                const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);

                if (!repository) {
                    return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<
                        EntityCollection<keyof EntitySchema.Entities>
                    >;
                }

                const mergedContext = { ...Shopwell.Context.api, ...context };

                try {
                    const result = await repository.search(criteria, mergedContext);
                    filterContext(result, context);
                    return result;
                } catch (e) {
                    return Promise.reject(e);
                }
            } catch (error) {
                return Promise.reject(error);
            }
        },
    );

    Shopwell.ExtensionAPI.handle(
        'repositoryGet',
        ({ entityName, id, criteria = new Shopwell.Data.Criteria(), context }, additionalInformation) => {
            const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
            if (!repository) {
                return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<null>;
            }

            const mergedContext = { ...Shopwell.Context.api, ...context };

            const result = repository.get(id, mergedContext, criteria);
            filterContext(result, context);
            return result;
        },
    );

    Shopwell.ExtensionAPI.handle('repositorySave', ({ entityName, entity, context }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<void>;
        }

        const mergedContext = { ...Shopwell.Context.api, ...context };

        return repository.save(entity as Entity<keyof EntitySchema.Entities>, mergedContext) as Promise<void>;
    });

    Shopwell.ExtensionAPI.handle('repositoryClone', ({ entityName, behavior, entityId, context }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities);
        }

        const mergedContext = { ...Shopwell.Context.api, ...context };

        const result = repository.clone(entityId, behavior as $TSDangerUnknownObject, mergedContext);
        filterContext(result, context);
        return result;
    });

    Shopwell.ExtensionAPI.handle('repositoryHasChanges', ({ entityName, entity }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<boolean>;
        }

        return repository.hasChanges(entity as Entity<keyof EntitySchema.Entities>);
    });

    Shopwell.ExtensionAPI.handle('repositorySaveAll', ({ entityName, entities, context }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<void>;
        }

        const mergedContext = { ...Shopwell.Context.api, ...context };

        return repository.saveAll(entities as EntityCollection<keyof EntitySchema.Entities>, mergedContext) as Promise<void>;
    });

    Shopwell.ExtensionAPI.handle('repositoryDelete', ({ entityName, entityId, context }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<void>;
        }

        const mergedContext = { ...Shopwell.Context.api, ...context };

        return repository.delete(entityId, mergedContext) as unknown as Promise<void>;
    });

    Shopwell.ExtensionAPI.handle('repositoryCreate', ({ entityName, entityId, context }, additionalInformation) => {
        const repository = getRepository(entityName as keyof EntitySchema.Entities, additionalInformation);
        if (!repository) {
            return rejectRepositoryCreation(entityName as keyof EntitySchema.Entities) as Promise<
                Entity<keyof EntitySchema.Entities>
            >;
        }

        const mergedContext = { ...Shopwell.Context.api, ...context };

        const result = repository.create(mergedContext, entityId);
        filterContext(result, context);
        return result;
    });
}
