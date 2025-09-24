#
# Use a local vector store and RAG for an LLM query
#
# Arg   Usage
# 1     database ID
# 2     Text

import sys
from llama_cpp import Llama
from langchain.llms import CTransformers
from langchain.embeddings import HuggingFaceEmbeddings
from langchain.vectorstores import FAISS
from langchain.prompts import PromptTemplate
from langchain.chains import RetrievalQA

# Prepare template for prompting

Template = """Use the following pieces of information to answer the user's question.
If you don't know the answer, just say that you don't know, don't try to make up an answer.
Context: {context}
Question: {question}
Only return the helpful answer below and nothing else.
Helpful answer:
"""

DatabaseID = sys.argv[1]
LLM = Llama(model_path="/mnt/data/llama_cpp_models/Meta-Llama-3-8B-Instruct-Q4_0.gguf", n_threads=4, n_threads_batch=4, n_ctx=16384, chat_format="llama-3")

Embeddings = HuggingFaceEmbeddings(model_name="/mnt/data/llm_prog/sentence_transformers/all-MiniLM-L6-v2",model_kwargs={'device':'cpu'})
Db = FAISS.load_local("/home/stparch/llmdata/faiss/" + DatabaseID,Embeddings,allow_dangerous_deserialization=True,normalize_L2=True)

UserMessage = sys.argv[2]
#SimilarityResults = Db.similarity_search_with_score(query=sys.argv[2],k=8)
LocalRetriever = Db.as_retriever(search_type="mmr", search_kwargs={'k': 5, 'fetch_k': 100, 'lambda_mult': 0.5})
SimilarityResults = LocalRetriever.invoke(UserMessage)

#Messages = [
#    {"role": "system", "content": "Use the following pieces of information to answer the user's question.  If you don't know the answer, just say that you don't know, don't try to make up an answer."},
#    {"role": "user", "content": f"Context: {SimilarityResults}\n\nQuestion: {UserMessage}"}
#]

Messages = [
    {"role": "system", "content": "You are a helpful assistant"},
    {"role": "user", "content": f"Use the following pieces of information to answer the user's question. If you don't know the answer, just say that you don't know, don't try to make up an answer.Context: {SimilarityResults}\n\nQuestion: {UserMessage}"}
]


ChatResponse = LLM.create_chat_completion(messages=Messages,stream=True)

# Chat completion
print("<llm_text>",flush=True)
for Item in ChatResponse:
    if 'content' in Item['choices'][0]['delta']:
        print(Item['choices'][0]['delta']['content'],end='',flush=True)

print("</llm_text>",flush=True)

# Similarity search results
print("<llm_doc_matches>")
Score = 1.0
for Doc in SimilarityResults:
    print("<llm_doc>")
    print(f"<score>{Score:3f}</score>")
    print("<content>")
    print(Doc.page_content)
    print("</content>")
    print("<metadata>")
    print(Doc.metadata)
    print("</metadata>")
    print("</llm_doc>")

print("</llm_doc_matches>")
sys.exit()


