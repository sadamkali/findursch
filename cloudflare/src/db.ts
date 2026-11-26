import { Client, type QueryResult, type QueryResultRow } from "pg";

export interface HyperdriveBinding {
  connectionString: string;
}

export interface DatabaseEnv {
  HYPERDRIVE?: HyperdriveBinding;
}

export class DatabaseUnavailableError extends Error {
  constructor() {
    super("The Hyperdrive database binding has not been configured.");
    this.name = "DatabaseUnavailableError";
  }
}

export async function withDatabase<T>(
  env: DatabaseEnv,
  work: (client: Client) => Promise<T>,
): Promise<T> {
  const connectionString = env.HYPERDRIVE?.connectionString;

  if (!connectionString) {
    throw new DatabaseUnavailableError();
  }

  const client = new Client({
    connectionString,
    connectionTimeoutMillis: 5_000,
    statement_timeout: 7_000,
  });

  await client.connect();

  try {
    return await work(client);
  } finally {
    await client.end().catch(() => undefined);
  }
}

export async function queryRows<T extends QueryResultRow>(
  client: Client,
  text: string,
  values: unknown[] = [],
): Promise<QueryResult<T>> {
  return client.query<T>(text, values);
}
